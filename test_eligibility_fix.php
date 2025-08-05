<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Testing fixed eligibility system...\n\n";

try {
    // Get the test scheme
    $scheme = \App\Models\GovernmentScheme::find(7); // Income Certificate Holder Support Program
    
    if (!$scheme) {
        echo "❌ Test scheme not found!\n";
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
        
        // Get user's income using the new method
        $userIncome = $scheme->getUserIncome($user);
        echo "💰 Income: ₹" . number_format($userIncome) . "\n";
        
        // Check eligibility
        $isEligible = $scheme->checkEligibility($user);
        echo "✅ Eligible: " . ($isEligible ? 'Yes' : 'No') . "\n";
        
        // Get detailed eligibility info
        $eligibilityDetails = $scheme->getEligibilityDetails($user);
        echo "📊 Eligibility Details:\n";
        
        foreach ($eligibilityDetails['checks'] as $check => $details) {
            echo "   {$check}: {$details['user_value']} | Required: {$details['required']} | Eligible: " . ($details['eligible'] ? 'Yes' : 'No') . "\n";
        }
        
        echo "\n" . str_repeat("-", 60) . "\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} 