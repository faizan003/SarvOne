<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Opportunity Hub Default Filter ===\n\n";

// Get a user with VCs
$user = \App\Models\User::where('verification_status', 'verified')
    ->whereNotNull('verified_at')
    ->first();

if (!$user) {
    echo "❌ No verified user found!\n";
    exit(1);
}

echo "👤 User: {$user->name} (DID: {$user->did})\n\n";

// Get all schemes
$allSchemes = \App\Models\GovernmentScheme::where('status', 'active')->get();
echo "📋 Total Active Schemes: " . $allSchemes->count() . "\n\n";

// Check eligibility for each scheme
$eligibleSchemes = [];
$notEligibleSchemes = [];

foreach ($allSchemes as $scheme) {
    $eligibilityDetails = $scheme->getEligibilityDetails($user);
    
    if ($eligibilityDetails['eligible']) {
        $eligibleSchemes[] = $scheme;
        echo "✅ ELIGIBLE: {$scheme->scheme_name}\n";
        echo "   Category: {$scheme->category}\n";
        echo "   Benefit: {$scheme->benefit_type} - ₹" . number_format($scheme->benefit_amount) . "\n";
        echo "   Missing Criteria: " . (empty($eligibilityDetails['missing_criteria']) ? 'None' : implode(', ', $eligibilityDetails['missing_criteria'])) . "\n\n";
    } else {
        $notEligibleSchemes[] = $scheme;
        echo "❌ NOT ELIGIBLE: {$scheme->scheme_name}\n";
        echo "   Category: {$scheme->category}\n";
        echo "   Missing Criteria: " . implode(', ', $eligibilityDetails['missing_criteria']) . "\n\n";
    }
}

echo "📊 Summary:\n";
echo "   Total Schemes: " . $allSchemes->count() . "\n";
echo "   Eligible Schemes: " . count($eligibleSchemes) . "\n";
echo "   Not Eligible Schemes: " . count($notEligibleSchemes) . "\n\n";

if (count($eligibleSchemes) > 0) {
    echo "🎯 With default 'Eligible Only' filter, the user will see:\n";
    foreach ($eligibleSchemes as $scheme) {
        echo "   • {$scheme->scheme_name}\n";
    }
} else {
    echo "⚠️ User is not eligible for any schemes. The opportunity hub will show empty state.\n";
}

echo "\n=== Test Complete ===\n"; 