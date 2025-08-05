<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Testing Simple IPFS Upload and Aadhaar VC Issuance...\n\n";

try {
    // Test 1: Simple IPFS Upload
    echo "📋 Test 1: Testing simple IPFS upload...\n";
    
    $pinataJwt = env('PINATA_JWT_KEY');
    $endpoint = 'https://api.pinata.cloud/pinning/pinFileToIPFS';
    
    // Test JSON content
    $testData = [
        'test' => 'data',
        'timestamp' => now()->toISOString(),
        'message' => 'Hello from SarvOne!'
    ];
    
    $jsonContent = json_encode($testData, JSON_PRETTY_PRINT);
    $fileName = 'test_' . uniqid() . '.json';
    
    echo "   Uploading test file: $fileName\n";
    echo "   Content length: " . strlen($jsonContent) . " bytes\n";
    echo "   JWT token length: " . strlen($pinataJwt) . " characters\n";
    
    // Create a temporary file for upload
    $tempFile = tmpfile();
    $tempPath = stream_get_meta_data($tempFile)['uri'];
    fwrite($tempFile, $jsonContent);
    
    // Upload to Pinata using cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $pinataJwt,
        'Content-Type: multipart/form-data'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'file' => new \CURLFile($tempPath, 'application/json', $fileName)
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $responseBody = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    fclose($tempFile);
    
    echo "   HTTP Code: $httpCode\n";
    echo "   Response: $responseBody\n";
    
    if ($httpCode === 200) {
        $data = json_decode($responseBody, true);
        if ($data && isset($data['IpfsHash'])) {
            echo "   ✅ IPFS upload successful! CID: {$data['IpfsHash']}\n\n";
        } else {
            echo "   ❌ IPFS upload failed: Invalid response format\n\n";
        }
    } else {
        echo "   ❌ IPFS upload failed: HTTP $httpCode\n";
        echo "   Error: $error\n\n";
    }
    
    // Test 2: Aadhaar VC Issuance (without blockchain)
    echo "📋 Test 2: Testing Aadhaar VC issuance (without blockchain)...\n";
    
    // Create a test user
    $testUser = new \App\Models\User();
    $testUser->name = 'Test User ' . time();
    $testUser->phone = '+91' . rand(6000000000, 9999999999);
    $testUser->verification_status = 'verified';
    $testUser->aadhaar_number = '123456789012';
    $testUser->did = 'did:sarvone:' . bin2hex(random_bytes(16));
    $testUser->save();
    
    echo "   Created test user: {$testUser->name} (DID: {$testUser->did})\n";
    
    // Test Aadhaar VC issuance
    $aadhaarService = app(\App\Services\AadhaarSimulationService::class);
    $aadhaarResult = $aadhaarService->simulateAadhaarVerification(
        $testUser, 
        $testUser->aadhaar_number, 
        $testUser->name
    );
    
    if ($aadhaarResult['success']) {
        echo "   ✅ Aadhaar VC issued successfully!\n";
        echo "   Credential ID: {$aadhaarResult['credential_id']}\n";
        echo "   Transaction Hash: {$aadhaarResult['transaction_hash']}\n";
        
        // Check if VC was saved in database
                 $savedVC = \App\Models\VerifiableCredential::where('vc_id', $aadhaarResult['credential_id'])->first();
        if ($savedVC) {
                         echo "   ✅ VC saved in database with ID: {$savedVC->id}\n";
             echo "   VC ID: {$savedVC->vc_id}\n";
             echo "   Credential Type: {$savedVC->vc_type}\n";
             echo "   Status: {$savedVC->status}\n";
        } else {
            echo "   ❌ VC not found in database\n";
        }
        
    } else {
        echo "   ❌ Aadhaar VC issuance failed: {$aadhaarResult['message']}\n";
        if (isset($aadhaarResult['error'])) {
            echo "   Error details: {$aadhaarResult['error']}\n";
        }
    }
    
    // Clean up
    echo "\n🧹 Cleaning up test data...\n";
    if (isset($savedVC)) {
        $savedVC->delete();
        echo "   ✅ Test VC deleted\n";
    }
    $testUser->delete();
    echo "   ✅ Test user deleted\n";
    
    echo "\n🎉 Simple IPFS and Aadhaar VC test completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 