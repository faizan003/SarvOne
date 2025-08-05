<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing FastAPI VC Issuance ===\n\n";

// Test data
$userDID = 'did:sarvone:9e8fc2b8c4d21df8';
$vcHash = hash('sha256', 'test_credential_data_' . time());
$vcType = 'marksheet';
$orgPrivateKey = 'd143f545d1cb2e055f77ca8a2102daaa90ab247237537ff1fa3b9759c696af3b';
$orgAddress = '0xd1721277678813c394b824011505EBD6C9ae7039';
$orgDID = 'did:sarvone:org:dho01:t0hqg2:003:de4b';

echo "📋 Test Parameters:\n";
echo "   User DID: {$userDID}\n";
echo "   VC Hash: {$vcHash}\n";
echo "   VC Type: {$vcType}\n";
echo "   Org DID: {$orgDID}\n";
echo "   Org Address: {$orgAddress}\n";
echo "   Org Private Key: " . substr($orgPrivateKey, 0, 10) . "...\n\n";

// Test 1: Check FastAPI service health
echo "1. Testing FastAPI service health...\n";
try {
    $response = \Illuminate\Support\Facades\Http::timeout(10)
        ->get('http://localhost:8003/health');
    
    if ($response->successful()) {
        echo "   ✅ FastAPI service is healthy\n";
        echo "   Response: " . $response->body() . "\n";
    } else {
        echo "   ❌ FastAPI service health check failed\n";
        echo "   Status: " . $response->status() . "\n";
        echo "   Response: " . $response->body() . "\n";
    }
} catch (\Exception $e) {
    echo "   ❌ FastAPI service connection failed: " . $e->getMessage() . "\n";
}

echo "\n2. Testing VC issuance with current parameters...\n";
try {
    $requestData = [
        'user_did' => $userDID,
        'vc_hash' => $vcHash,
        'vc_type' => $vcType,
        'org_private_key' => $orgPrivateKey,
        'property_id' => ''  // Add property_id parameter
    ];
    
    echo "   📤 Sending request data:\n";
    echo "   " . json_encode($requestData, JSON_PRETTY_PRINT) . "\n\n";
    
    $response = \Illuminate\Support\Facades\Http::timeout(30)
        ->post('http://localhost:8003/issue_vc', $requestData);
    
    if ($response->successful()) {
        echo "   ✅ VC issuance successful!\n";
        echo "   Response: " . $response->body() . "\n";
    } else {
        echo "   ❌ VC issuance failed\n";
        echo "   Status: " . $response->status() . "\n";
        echo "   Response: " . $response->body() . "\n";
    }
} catch (\Exception $e) {
    echo "   ❌ VC issuance error: " . $e->getMessage() . "\n";
}

echo "\n3. Testing with corrected parameters (bytes32 conversion)...\n";
try {
    // Convert string DIDs to bytes32 format
    $userDIDBytes32 = '0x' . str_pad(substr(hash('sha256', $userDID), 0, 64), 64, '0');
    $vcHashBytes32 = '0x' . str_pad($vcHash, 64, '0');
    
    $requestData = [
        'user_did' => $userDIDBytes32,
        'vc_hash' => $vcHashBytes32,
        'vc_type' => $vcType,
        'property_id' => '' // Empty string for non-property VCs
    ];
    
    echo "   📤 Sending corrected request data:\n";
    echo "   " . json_encode($requestData, JSON_PRETTY_PRINT) . "\n\n";
    
    $response = \Illuminate\Support\Facades\Http::timeout(30)
        ->post('http://localhost:8003/issue_vc', $requestData);
    
    if ($response->successful()) {
        echo "   ✅ VC issuance with corrected parameters successful!\n";
        echo "   Response: " . $response->body() . "\n";
    } else {
        echo "   ❌ VC issuance with corrected parameters failed\n";
        echo "   Status: " . $response->status() . "\n";
        echo "   Response: " . $response->body() . "\n";
    }
} catch (\Exception $e) {
    echo "   ❌ VC issuance with corrected parameters error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n"; 