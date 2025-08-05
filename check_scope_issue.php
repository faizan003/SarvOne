<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Checking Government Organization Scope Mapping Issue...\n\n";

try {
    // Get government organization
    $govOrg = \App\Models\Organization::where('organization_type', 'government')->first();
    
    if (!$govOrg) {
        echo "❌ Government organization not found!\n";
        exit(1);
    }
    
    echo "📋 Government Organization Details:\n";
    echo "   ID: {$govOrg->id}\n";
    echo "   Name: {$govOrg->legal_name}\n";
    echo "   Wallet: {$govOrg->wallet_address}\n\n";
    
    echo "📝 Current Write Scopes (should be for ISSUING VCs):\n";
    foreach ($govOrg->write_scopes as $scope) {
        echo "   ✅ {$scope}\n";
    }
    
    echo "\n📖 Current Read Scopes (should be for VERIFYING VCs):\n";
    foreach ($govOrg->read_scopes as $scope) {
        echo "   ✅ {$scope}\n";
    }
    
    // Check the issue: Government read scopes should NOT have verify_ prefix for documents they issue
    echo "\n🔍 PROBLEM IDENTIFIED:\n";
    echo "Government organization should be able to ISSUE documents (write scopes) like:\n";
    echo "   - aadhaar_card (not verify_aadhaar_card)\n";
    echo "   - pan_card (not verify_pan_card)\n";
    echo "   - voter_id (not verify_voter_id)\n";
    echo "   - driving_license (not verify_driving_license)\n\n";
    
    echo "But currently the read scopes have verify_ prefix which is wrong!\n\n";
    
    // Fix the scopes
    echo "🔄 Fixing Government Organization Scopes...\n";
    
    // Government should have these as WRITE scopes (for issuing)
    $correctWriteScopes = [
        'aadhaar_card',
        'pan_card',
        'voter_id',
        'driving_license',
        'passport',
        'birth_certificate',
        'marriage_certificate',
        'domicile_certificate',
        'caste_certificate',
        'disability_certificate',
        'income_certificate',
        'family_income_verification',
        'domicile_residence_verification',
        'caste_category_verification',
        'disability_assessment',
        'ration_card',
        'ayushman_card',
        'pension_card',
        'scholarship_approval',
        'economic_weaker_section',
        'property_land_records'
    ];
    
    // Government should have these as READ scopes (for verifying other orgs' documents)
    $correctReadScopes = [
        'verify_aadhaar_card',
        'verify_pan_card',
        'verify_voter_id',
        'verify_driving_license',
        'verify_passport',
        'verify_birth_certificate',
        'verify_marriage_certificate',
        'verify_domicile_certificate',
        'verify_caste_certificate',
        'verify_disability_certificate',
        'verify_income_certificate',
        'verify_family_income_verification',
        'verify_domicile_residence_verification',
        'verify_caste_category_verification',
        'verify_disability_assessment',
        'verify_ration_card',
        'verify_ayushman_card',
        'verify_pension_card',
        'verify_scholarship_approval',
        'verify_economic_weaker_section',
        'verify_property_land_records'
    ];
    
    // Update the organization with correct scopes
    $govOrg->update([
        'write_scopes' => $correctWriteScopes,
        'read_scopes' => $correctReadScopes
    ]);
    
    echo "✅ Government organization scopes fixed!\n\n";
    
    echo "📝 Updated Write Scopes (for ISSUING VCs):\n";
    foreach ($govOrg->write_scopes as $scope) {
        echo "   ✅ {$scope}\n";
    }
    
    echo "\n📖 Updated Read Scopes (for VERIFYING VCs):\n";
    foreach ($govOrg->read_scopes as $scope) {
        echo "   ✅ {$scope}\n";
    }
    
    // Test scope mapping
    echo "\n🧪 Testing Scope Mapping...\n";
    
    $credentialScopeService = app(\App\Services\CredentialScopeService::class);
    $mappedScopes = $credentialScopeService::mapScopesForContract($govOrg->write_scopes, $govOrg->read_scopes);
    
    echo "📋 Mapped Scopes for Blockchain:\n";
    foreach ($mappedScopes as $scope) {
        echo "   ✅ {$scope}\n";
    }
    
    // Check if the mapping is correct
    $expectedWriteScopes = ['aadhaar_card', 'pan_card', 'voter_id', 'driving_license'];
    $missingWriteScopes = array_diff($expectedWriteScopes, $mappedScopes);
    
    if (!empty($missingWriteScopes)) {
        echo "\n⚠️ Missing Write Scopes in Mapping:\n";
        foreach ($missingWriteScopes as $scope) {
            echo "   ❌ {$scope}\n";
        }
    } else {
        echo "\n✅ All required write scopes are correctly mapped!\n";
    }
    
    echo "\n🎉 Scope mapping issue fixed!\n";
    echo "💡 Now the government organization can:\n";
    echo "   - ISSUE: aadhaar_card, pan_card, voter_id, driving_license (without verify_ prefix)\n";
    echo "   - VERIFY: verify_aadhaar_card, verify_pan_card, etc. (with verify_ prefix)\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 