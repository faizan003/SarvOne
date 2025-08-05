<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Testing UIDAI Organization Scope Mapping...\n\n";

try {
    // Test UIDAI scopes
    $uidaiWriteScopes = ['aadhaar_card'];
    $uidaiReadScopes = ['aadhaar_card'];
    
    echo "📋 UIDAI Organization Scopes:\n";
    echo "   Write Scopes: " . implode(', ', $uidaiWriteScopes) . "\n";
    echo "   Read Scopes: " . implode(', ', $uidaiReadScopes) . "\n\n";
    
    // Test scope mapping
    $credentialScopeService = app(\App\Services\CredentialScopeService::class);
    $mappedScopes = $credentialScopeService::mapScopesForContract($uidaiWriteScopes, $uidaiReadScopes);
    
    echo "📋 Mapped Scopes for Blockchain:\n";
    foreach ($mappedScopes as $scope) {
        echo "   ✅ {$scope}\n";
    }
    
    // Check if the mapping is correct
    $expectedScopes = ['aadhaar_card'];
    $missingScopes = array_diff($expectedScopes, $mappedScopes);
    $unexpectedScopes = array_diff($mappedScopes, $expectedScopes);
    
    if (!empty($missingScopes)) {
        echo "\n⚠️ Missing Expected Scopes:\n";
        foreach ($missingScopes as $scope) {
            echo "   ❌ {$scope}\n";
        }
    }
    
    if (!empty($unexpectedScopes)) {
        echo "\n⚠️ Unexpected Scopes:\n";
        foreach ($unexpectedScopes as $scope) {
            echo "   ❌ {$scope}\n";
        }
    }
    
    if (empty($missingScopes) && empty($unexpectedScopes)) {
        echo "\n✅ UIDAI scope mapping is correct!\n";
        echo "   - aadhaar_card maps to aadhaar_card (not verify_aadhaar_card)\n";
    }
    
    // Test with different organization types for comparison
    echo "\n🔍 Comparing with other organization types:\n";
    
    // Government organization (should have both write and read scopes)
    $govWriteScopes = ['aadhaar_card', 'pan_card'];
    $govReadScopes = ['verify_aadhaar_card', 'verify_pan_card'];
    
    echo "\n📋 Government Organization:\n";
    echo "   Write: " . implode(', ', $govWriteScopes) . "\n";
    echo "   Read: " . implode(', ', $govReadScopes) . "\n";
    
    $govMappedScopes = $credentialScopeService::mapScopesForContract($govWriteScopes, $govReadScopes);
    echo "   Mapped: " . implode(', ', $govMappedScopes) . "\n";
    
    // Bank organization (should have verify_ prefix for read scopes)
    $bankWriteScopes = ['loan_approval'];
    $bankReadScopes = ['kyc_identity'];
    
    echo "\n📋 Bank Organization:\n";
    echo "   Write: " . implode(', ', $bankWriteScopes) . "\n";
    echo "   Read: " . implode(', ', $bankReadScopes) . "\n";
    
    $bankMappedScopes = $credentialScopeService::mapScopesForContract($bankWriteScopes, $bankReadScopes);
    echo "   Mapped: " . implode(', ', $bankMappedScopes) . "\n";
    
    echo "\n🎉 UIDAI scope mapping test completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 