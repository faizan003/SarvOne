<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Testing UIDAI Aadhaar VC Issuance...\n\n";

try {
    // Create a test UIDAI organization
    echo "📋 Creating test UIDAI organization...\n";
    $uidaiOrg = new \App\Models\Organization();
    $uidaiOrg->legal_name = 'UIDAI Test Authority';
    $uidaiOrg->organization_type = 'uidai';
    $uidaiOrg->registration_number = 'UIDAI_TEST_001';
    $uidaiOrg->official_email = 'test_uidai_' . time() . '@uidai.gov.in';
    $uidaiOrg->official_phone = '9876543210';
    $uidaiOrg->head_office_address = 'UIDAI Headquarters, New Delhi';
    $uidaiOrg->signatory_name = 'UIDAI Director';
    $uidaiOrg->signatory_designation = 'Director';
    $uidaiOrg->signatory_email = 'director@uidai.gov.in';
    $uidaiOrg->signatory_phone = '9876543211';
    $uidaiOrg->wallet_address = '0x1234567890123456789012345678901234567890';
    $uidaiOrg->expected_volume = '1-50';
    $uidaiOrg->use_case_description = 'Aadhaar card issuance and verification';
    $uidaiOrg->terms_agreement = true;
    $uidaiOrg->password = bcrypt('password123');
    $uidaiOrg->write_scopes = ['aadhaar_card'];
    $uidaiOrg->read_scopes = ['aadhaar_card'];
    $uidaiOrg->verification_status = 'approved';
    
    $uidaiOrg->save();
    echo "✅ UIDAI organization created with ID: {$uidaiOrg->id}\n\n";
    
    // Get a test user
    $testUser = \App\Models\User::whereNotNull('did')->first();
    if (!$testUser) {
        echo "❌ No test user with DID found. Creating one...\n";
        $testUser = new \App\Models\User();
        $testUser->name = 'Test User';
        $testUser->email = 'test_user_' . time() . '@test.com';
        $testUser->phone = '9876543212';
        $testUser->did = 'did:sarvone:' . bin2hex(random_bytes(16));
        $testUser->password = bcrypt('password123');
        $testUser->save();
        echo "✅ Test user created with DID: {$testUser->did}\n";
    } else {
        echo "✅ Using existing test user: {$testUser->name} (DID: {$testUser->did})\n";
    }
    
    // Test Aadhaar VC issuance
    echo "\n📋 Testing Aadhaar VC issuance...\n";
    
    $credentialService = app(\App\Services\CredentialService::class);
    
    // Aadhaar data
    $aadhaarData = [
        'aadhaar_number' => '123456789012',
        'name' => $testUser->name,
        'date_of_birth' => '1995-06-15',
        'gender' => 'Male',
        'address' => '123 Test Street, New Delhi, Delhi - 110001',
        'photo_url' => 'https://example.com/photo.jpg',
        'issued_date' => now()->format('Y-m-d'),
        'valid_until' => now()->addYears(10)->format('Y-m-d')
    ];
    
    echo "   Aadhaar Number: {$aadhaarData['aadhaar_number']}\n";
    echo "   Name: {$aadhaarData['name']}\n";
    echo "   DOB: {$aadhaarData['date_of_birth']}\n";
    
    // Issue the credential
    $result = $credentialService->issueCredential(
        $uidaiOrg,
        $testUser,
        'aadhaar_card',
        $aadhaarData,
        env('GOVERNMENT_PRIVATE_KEY')
    );
    
    if ($result['success']) {
        echo "\n✅ Aadhaar VC issued successfully!\n";
        echo "   Credential ID: {$result['credential_id']}\n";
        echo "   IPFS CID: {$result['ipfs_cid']}\n";
        echo "   Transaction Hash: {$result['transaction_hash']}\n";
        
        // Verify the VC was saved in database
        $savedVC = \App\Models\VerifiableCredential::where('credential_id', $result['credential_id'])->first();
        if ($savedVC) {
            echo "\n📋 Database verification:\n";
            echo "   ✅ VC saved in database with ID: {$savedVC->id}\n";
            echo "   ✅ Issuer Organization: {$savedVC->issuer_organization_id}\n";
            echo "   ✅ Recipient User: {$savedVC->recipient_user_id}\n";
            echo "   ✅ Credential Type: {$savedVC->credential_type}\n";
            echo "   ✅ Blockchain Hash: {$savedVC->blockchain_hash}\n";
            echo "   ✅ IPFS CID: {$savedVC->ipfs_cid}\n";
            
            // Check if the credential data is correct
            $credentialData = json_decode($savedVC->credential_data, true);
            if ($credentialData && isset($credentialData['aadhaar_number'])) {
                echo "   ✅ Aadhaar Number in DB: {$credentialData['aadhaar_number']}\n";
                echo "   ✅ Name in DB: {$credentialData['name']}\n";
            } else {
                echo "   ❌ Credential data not properly saved\n";
            }
            
            // Check if the scope was saved correctly (should be 'aadhaar_card', not 'verify_aadhaar_card')
            if ($savedVC->credential_type === 'aadhaar_card') {
                echo "   ✅ Credential type saved correctly as 'aadhaar_card'\n";
            } else {
                echo "   ❌ Credential type incorrectly saved as: {$savedVC->credential_type}\n";
            }
            
        } else {
            echo "   ❌ VC not found in database!\n";
        }
        
    } else {
        echo "\n❌ Aadhaar VC issuance failed: {$result['message']}\n";
        if (isset($result['error'])) {
            echo "   Error details: " . json_encode($result['error']) . "\n";
        }
    }
    
    // Clean up
    echo "\n🧹 Cleaning up test data...\n";
    if (isset($savedVC)) {
        $savedVC->delete();
        echo "   ✅ Test VC deleted\n";
    }
    $uidaiOrg->delete();
    echo "   ✅ Test UIDAI organization deleted\n";
    
    echo "\n🎉 UIDAI Aadhaar VC issuance test completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 