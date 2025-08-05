<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 Creating Government Organization for Aadhaar simulation...\n\n";

try {
    // Check if government organization already exists
    $existingGov = \App\Models\Organization::where('organization_type', 'government')->first();
    
    if ($existingGov) {
        echo "⚠️ Government organization already exists:\n";
        echo "   ID: {$existingGov->id}\n";
        echo "   Name: {$existingGov->legal_name}\n";
        echo "   Wallet: {$existingGov->wallet_address}\n";
        echo "   Status: {$existingGov->verification_status}\n\n";
        
        // Update the existing organization with correct scopes
        echo "🔄 Updating existing government organization with correct scopes...\n";
        
        $existingGov->update([
            'write_scopes' => [
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
            ],
            'read_scopes' => [
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
            ],
            'verification_status' => 'approved',
            'verified_at' => now()
        ]);
        
        echo "✅ Government organization updated successfully!\n";
        $govOrg = $existingGov;
        
    } else {
        echo "🆕 Creating new government organization...\n";
        
        // Create UIDAI organization
        $uidaiOrg = \App\Models\Organization::create([
            'legal_name' => 'Unique Identification Authority of India (UIDAI)',
            'organization_type' => 'uidai',
            'registration_number' => 'GOV-UIDAI-2024-001',
            'official_email' => 'admin@uidai.gov.in',
            'official_phone' => '+91-11-23456789',
            'website_url' => 'https://uidai.gov.in',
            'head_office_address' => 'UIDAI Headquarters, New Delhi, India',
            'branch_address' => 'UIDAI Regional Office, Mumbai, Maharashtra',
            'signatory_name' => 'Dr. Ajay Bhushan Pandey',
            'signatory_designation' => 'CEO, UIDAI',
            'signatory_email' => 'ceo@uidai.gov.in',
            'signatory_phone' => '+91-11-23456790',
            'wallet_address' => env('GOVERNMENT_WALLET_ADDRESS', '0x4778eC77AC034d25687fAf8d9457b3f1FC4bB8De'),
            'technical_contact_name' => 'Technical Team UIDAI',
            'technical_contact_email' => 'tech@uidai.gov.in',
            'write_scopes' => [
                'aadhaar_card'
            ],
            'read_scopes' => [
                'aadhaar_card'
            ],
            'expected_volume' => '1000+',
            'use_case_description' => 'Government document issuance and verification for citizens',
            'password' => Hash::make('government@2024'),
            'verification_status' => 'approved',
            'verified_at' => now(),
            'verification_notes' => 'Government organization for Aadhaar simulation',
            'did' => 'did:sarvone:government:uidai',
            'trust_score' => 100.00,
            'terms_agreement' => true
        ]);
        
        echo "✅ UIDAI organization created successfully!\n";
    }
    
    // Create Government organization
    $govOrg = \App\Models\Organization::where('organization_type', 'government')->first();
    
    if (!$govOrg) {
        echo "🆕 Creating new government organization...\n";
        
        $govOrg = \App\Models\Organization::create([
            'legal_name' => 'Government of India',
            'organization_type' => 'government',
            'registration_number' => 'GOV-INDIA-2024-001',
            'official_email' => 'admin@gov.in',
            'official_phone' => '+91-11-23456789',
            'website_url' => 'https://gov.in',
            'head_office_address' => 'Government of India, New Delhi, India',
            'branch_address' => 'Government Regional Office, Mumbai, Maharashtra',
            'signatory_name' => 'Government Official',
            'signatory_designation' => 'Government Representative',
            'signatory_email' => 'official@gov.in',
            'signatory_phone' => '+91-11-23456790',
            'wallet_address' => env('GOVERNMENT_WALLET_ADDRESS', '0x4778eC77AC034d25687fAf8d9457b3f1FC4bB8De'),
            'technical_contact_name' => 'Technical Team Government',
            'technical_contact_email' => 'tech@gov.in',
            'write_scopes' => [
                'aadhaar_card', 'pan_card', 'voter_id', 'caste_certificate', 'ration_card',
                'income_certificate', 'domicile_certificate', 'birth_certificate',
                'death_certificate', 'marriage_certificate'
            ],
            'read_scopes' => [
                'aadhaar_card', 'pan_card', 'voter_id', 'caste_certificate', 'ration_card',
                'income_certificate', 'domicile_certificate', 'birth_certificate',
                'death_certificate', 'marriage_certificate'
            ],
            'expected_volume' => '1000+',
            'use_case_description' => 'Government document issuance and verification for citizens',
            'password' => Hash::make('government@2024'),
            'verification_status' => 'approved',
            'verified_at' => now(),
            'verification_notes' => 'Government organization for document issuance',
            'did' => 'did:sarvone:government:india',
            'trust_score' => 100.00,
            'terms_agreement' => true
        ]);
        
        echo "✅ Government organization created successfully!\n";
    }
    
    echo "\n📋 UIDAI Organization Details:\n";
    echo "   ID: {$uidaiOrg->id}\n";
    echo "   Name: {$uidaiOrg->legal_name}\n";
    echo "   Type: {$uidaiOrg->organization_type}\n";
    echo "   Wallet: {$uidaiOrg->wallet_address}\n";
    echo "   Status: {$uidaiOrg->verification_status}\n";
    echo "   DID: {$uidaiOrg->did}\n";
    echo "   Trust Score: {$uidaiOrg->trust_score}\n\n";
    
    echo "📋 Government Organization Details:\n";
    echo "   ID: {$govOrg->id}\n";
    echo "   Name: {$govOrg->legal_name}\n";
    echo "   Type: {$govOrg->organization_type}\n";
    echo "   Wallet: {$govOrg->wallet_address}\n";
    echo "   Status: {$govOrg->verification_status}\n";
    echo "   DID: {$govOrg->did}\n";
    echo "   Trust Score: {$govOrg->trust_score}\n\n";
    
    echo "📝 UIDAI Write Scopes (for issuing VCs):\n";
    foreach ($uidaiOrg->write_scopes as $scope) {
        echo "   ✅ {$scope}\n";
    }
    
    echo "\n📖 UIDAI Read Scopes (for verifying VCs):\n";
    foreach ($uidaiOrg->read_scopes as $scope) {
        echo "   ✅ {$scope}\n";
    }
    
    echo "\n📝 Government Write Scopes (for issuing VCs):\n";
    foreach ($govOrg->write_scopes as $scope) {
        echo "   ✅ {$scope}\n";
    }
    
    echo "\n📖 Government Read Scopes (for verifying VCs):\n";
    foreach ($govOrg->read_scopes as $scope) {
        echo "   ✅ {$scope}\n";
    }
    
    // Update .env file with government credentials
    echo "\n🔧 Updating .env file with government credentials...\n";
    
    $envFile = base_path('.env');
    $envContent = file_get_contents($envFile);
    
    // Add or update government credentials
    $envContent = preg_replace(
        '/GOVERNMENT_PRIVATE_KEY=.*/',
        'GOVERNMENT_PRIVATE_KEY=' . $govOrg->private_key,
        $envContent
    );
    
    $envContent = preg_replace(
        '/GOVERNMENT_WALLET_ADDRESS=.*/',
        'GOVERNMENT_WALLET_ADDRESS=' . $govOrg->wallet_address,
        $envContent
    );
    
    // If the lines don't exist, add them
    if (!str_contains($envContent, 'GOVERNMENT_PRIVATE_KEY=')) {
        $envContent .= "\nGOVERNMENT_PRIVATE_KEY=" . $govOrg->private_key;
    }
    
    if (!str_contains($envContent, 'GOVERNMENT_WALLET_ADDRESS=')) {
        $envContent .= "\nGOVERNMENT_WALLET_ADDRESS=" . $govOrg->wallet_address;
    }
    
    file_put_contents($envFile, $envContent);
    
    echo "✅ .env file updated with government credentials!\n\n";
    
    // Test Aadhaar simulation
    echo "🧪 Testing Aadhaar simulation...\n";
    
    $testUser = \App\Models\User::whereNotNull('did')->first();
    
    if ($testUser) {
        echo "👤 Testing with user: {$testUser->name} (DID: {$testUser->did})\n";
        
        $aadhaarService = app(\App\Services\AadhaarSimulationService::class);
        $result = $aadhaarService->simulateAadhaarVerification(
            $testUser,
            '123456789012', // Test Aadhaar number
            $testUser->name
        );
        
        if ($result['success']) {
            echo "✅ Aadhaar simulation test successful!\n";
            echo "   Credential ID: {$result['credential_id']}\n";
            echo "   Transaction Hash: {$result['transaction_hash']}\n";
            echo "   Aadhaar Data: " . json_encode($result['aadhaar_data'], JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "❌ Aadhaar simulation test failed: {$result['message']}\n";
        }
    } else {
        echo "❌ No users found for testing!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 