<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing 10th Passout Scholarship Scheme ===\n\n";

// Get the new scholarship scheme
$scholarshipScheme = \App\Models\GovernmentScheme::where('scheme_name', '10th Passout Student Scholarship Scheme')->first();

if (!$scholarshipScheme) {
    echo "❌ Scholarship scheme not found!\n";
    exit(1);
}

echo "📋 Scholarship Scheme Details:\n";
echo "   Name: {$scholarshipScheme->scheme_name}\n";
echo "   Category: {$scholarshipScheme->category}\n";
echo "   Max Income: ₹" . number_format($scholarshipScheme->max_income) . "\n";
echo "   Min Percentage: {$scholarshipScheme->min_percentage}%\n";
echo "   Required Credentials: " . implode(', ', $scholarshipScheme->required_credentials) . "\n";
echo "   Benefit Amount: ₹" . number_format($scholarshipScheme->benefit_amount) . "\n\n";

// Get a user with marksheet VC
$user = \App\Models\User::where('verification_status', 'verified')
    ->whereNotNull('verified_at')
    ->whereHas('verifiableCredentials', function($query) {
        $query->where('vc_type', 'marksheet');
    })
    ->first();

if (!$user) {
    echo "❌ No user with marksheet VC found!\n";
    exit(1);
}

echo "👤 User Found:\n";
echo "   Name: {$user->name}\n";
echo "   DID: {$user->did}\n";
echo "   Status: {$user->verification_status}\n\n";

// Get user's marksheet VC
$marksheetVC = $user->verifiableCredentials()->where('vc_type', 'marksheet')->first();

if ($marksheetVC) {
    $credentialData = is_string($marksheetVC->credential_data) 
        ? json_decode($marksheetVC->credential_data, true) 
        : $marksheetVC->credential_data;
    
    echo "📄 Marksheet Details:\n";
    echo "   Student: {$credentialData['student_name']}\n";
    echo "   School: {$credentialData['school_name']}\n";
    echo "   Class: {$credentialData['class']}\n";
    echo "   Percentage: {$credentialData['percentage']}%\n";
    echo "   Grade: {$credentialData['grade']}\n\n";
}

// Test eligibility
echo "🔍 Testing Eligibility...\n";
$eligibilityDetails = $scholarshipScheme->getEligibilityDetails($user);

echo "   Overall Eligibility: " . ($eligibilityDetails['eligible'] ? '✅ Eligible' : '❌ Not Eligible') . "\n\n";

if (!empty($eligibilityDetails['checks'])) {
    echo "   Detailed Checks:\n";
    foreach ($eligibilityDetails['checks'] as $checkType => $check) {
        $status = $check['eligible'] ? '✅' : '❌';
        echo "     {$status} {$checkType}: {$check['required']} | User: {$check['user_value']}\n";
    }
}

if (!empty($eligibilityDetails['missing_criteria'])) {
    echo "\n   Missing Criteria:\n";
    foreach ($eligibilityDetails['missing_criteria'] as $criteria) {
        echo "     ❌ {$criteria}\n";
    }
}

// Test the getUserPercentage method directly
echo "\n🔍 Testing Percentage Extraction:\n";
$userPercentage = $scholarshipScheme->getUserPercentage($user);
if ($userPercentage !== null) {
    echo "   Extracted Percentage: {$userPercentage}%\n";
    echo "   Required Percentage: {$scholarshipScheme->min_percentage}%\n";
    echo "   Meets Requirement: " . ($userPercentage >= $scholarshipScheme->min_percentage ? '✅ Yes' : '❌ No') . "\n";
} else {
    echo "   ❌ Could not extract percentage from marksheet VC\n";
}

echo "\n=== Test Complete ===\n"; 