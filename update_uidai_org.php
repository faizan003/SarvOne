<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 Updating UIDAI Organization with correct credentials...\n\n";

try {
    // Find the UIDAI organization
    $uidaiOrg = \App\Models\Organization::where('organization_type', 'government')
        ->orWhere('legal_name', 'LIKE', '%UIDAI%')
        ->orWhere('did', 'LIKE', '%uidai%')
        ->first();
    
    if ($uidaiOrg) {
        echo "📋 Found existing organization:\n";
        echo "   ID: {$uidaiOrg->id}\n";
        echo "   Name: {$uidaiOrg->legal_name}\n";
        echo "   Current DID: {$uidaiOrg->did}\n";
        echo "   Current Wallet: {$uidaiOrg->wallet_address}\n\n";
        
        // Update with correct credentials
        $uidaiOrg->update([
            'did' => 'did:sarvone:org:udiai:t0gtok:001:c3c1',
            'wallet_address' => '0xc6d34d55757e6594bC73cBFee80036fd8C6AAcf7',
            'private_key' => 'acdff23f0fe8b63b5033bc2fbe30372f9ea14bfaf8176df974d19e68cd252c37',
            'verification_status' => 'approved',
            'verified_at' => now(),
            'trust_score' => 100.00
        ]);
        
        echo "✅ UIDAI organization updated successfully!\n";
        echo "   New DID: {$uidaiOrg->did}\n";
        echo "   New Wallet: {$uidaiOrg->wallet_address}\n";
        echo "   Private Key: acdff23f0fe8b63b5033bc2fbe30372f9ea14bfaf8176df974d19e68cd252c37\n";
        echo "   Status: {$uidaiOrg->verification_status}\n";
        
    } else {
        echo "❌ No UIDAI organization found. Creating new one...\n";
        
        $uidaiOrg = \App\Models\Organization::create([
            'legal_name' => 'Unique Identification Authority of India (UIDAI)',
            'organization_type' => 'government',
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
            'wallet_address' => '0xc6d34d55757e6594bC73cBFee80036fd8C6AAcf7',
            'private_key' => 'acdff23f0fe8b63b5033bc2fbe30372f9ea14bfaf8176df974d19e68cd252c37',
            'technical_contact_name' => 'Technical Team UIDAI',
            'technical_contact_email' => 'tech@uidai.gov.in',
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
            'expected_volume' => '1000+',
            'use_case_description' => 'Government document issuance and verification for citizens',
            'password' => Hash::make('government@2024'),
            'verification_status' => 'approved',
            'verified_at' => now(),
            'verification_notes' => 'UIDAI organization for Aadhaar simulation',
            'did' => 'did:sarvone:org:udiai:t0gtok:001:c3c1',
            'trust_score' => 100.00,
            'terms_agreement' => true
        ]);
        
        echo "✅ UIDAI organization created successfully!\n";
    }
    
    echo "\n📋 UIDAI Organization Details:\n";
    echo "   ID: {$uidaiOrg->id}\n";
    echo "   Name: {$uidaiOrg->legal_name}\n";
    echo "   DID: {$uidaiOrg->did}\n";
    echo "   Wallet: {$uidaiOrg->wallet_address}\n";
    echo "   Status: {$uidaiOrg->verification_status}\n";
    echo "   Trust Score: {$uidaiOrg->trust_score}\n\n";
    
    // Test Aadhaar VC issuance
    echo "🧪 Testing Aadhaar VC issuance with updated organization...\n";
    
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
            echo "✅ Aadhaar VC issuance test successful!\n";
            echo "   Credential ID: {$result['credential_id']}\n";
            echo "   Transaction Hash: {$result['transaction_hash']}\n";
            echo "   Issuer Organization: {$uidaiOrg->legal_name}\n";
            echo "   Issuer DID: {$uidaiOrg->did}\n";
        } else {
            echo "❌ Aadhaar VC issuance test failed: {$result['message']}\n";
            if (isset($result['error'])) {
                echo "   Error details: {$result['error']}\n";
            }
        }
    } else {
        echo "❌ No users found for testing!\n";
    }
    
    echo "\n🎉 UIDAI organization update completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 