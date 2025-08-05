<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Testing Income Eligibility Fix...\n\n";

try {
    // Get a scheme with income criteria
    $scheme = \App\Models\GovernmentScheme::where('max_income', '>', 0)->first();
    
    if (!$scheme) {
        echo "❌ No scheme with income criteria found!\n";
        exit(1);
    }
    
    echo "📋 Testing scheme: {$scheme->scheme_name}\n";
    echo "💰 Max income: ₹" . number_format($scheme->max_income) . "\n\n";
    
    // Get users
    $users = \App\Models\User::whereNotNull('did')->get();
    
    foreach ($users as $user) {
        echo "👤 User: {$user->name} (ID: {$user->id})\n";
        echo "📱 Phone: {$user->phone}\n";
        echo "🆔 DID: {$user->did}\n";
        
        // Check if user has family_income in profile
        echo "💰 Profile Family Income: " . ($user->family_income ?? 'Not set') . "\n";
        
        // Check if user has income certificate VC
        $incomeVC = $user->verifiableCredentials()->where('vc_type', 'income_certificate')->first();
        if ($incomeVC) {
            echo "📄 Income Certificate VC: Found (ID: {$incomeVC->id})\n";
            if ($incomeVC->credential_data) {
                $data = is_string($incomeVC->credential_data) ? json_decode($incomeVC->credential_data, true) : $incomeVC->credential_data;
                echo "📊 VC Data: " . json_encode($data) . "\n";
            }
        } else {
            echo "📄 Income Certificate VC: Not found\n";
        }
        
        // Get user's income by checking eligibility details
        $eligibilityDetails = $scheme->getEligibilityDetails($user);
        $incomeCheck = $eligibilityDetails['checks']['income'] ?? null;
        $userIncome = null;
        if ($incomeCheck) {
            if (strpos($incomeCheck['user_value'], 'Income data not available') !== false) {
                $userIncome = null;
            } else {
                // Extract income from the formatted string "₹X,XXX"
                preg_match('/₹([0-9,]+)/', $incomeCheck['user_value'], $matches);
                if (isset($matches[1])) {
                    $userIncome = (int) str_replace(',', '', $matches[1]);
                }
            }
        }
        echo "💰 Calculated Income: " . ($userIncome === null ? 'NULL (No income data)' : '₹' . number_format($userIncome)) . "\n";
        
        // Check eligibility
        $isEligible = $scheme->checkEligibility($user);
        echo "✅ Eligible: " . ($isEligible ? 'Yes' : 'No') . "\n";
        
        // Get detailed eligibility info
        $eligibilityDetails = $scheme->getEligibilityDetails($user);
        echo "📊 Eligibility Details:\n";
        
        foreach ($eligibilityDetails['checks'] as $check => $details) {
            echo "   {$check}: {$details['user_value']} | Required: {$details['required']} | Eligible: " . ($details['eligible'] ? 'Yes' : 'No') . "\n";
        }
        
        if (!empty($eligibilityDetails['missing_criteria'])) {
            echo "❌ Missing Criteria: " . implode(', ', $eligibilityDetails['missing_criteria']) . "\n";
        }
        
        echo "\n" . str_repeat("-", 80) . "\n\n";
    }
    
    echo "✅ Test completed! Users without income certificates should now be ineligible.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 