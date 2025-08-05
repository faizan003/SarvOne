<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Testing Aadhaar Simulation with Updated Government Organization...\n\n";

try {
    // Get government organization
    $govOrg = \App\Models\Organization::where('organization_type', 'government')->first();
    
    if (!$govOrg) {
        echo "❌ Government organization not found!\n";
        exit(1);
    }
    
    echo "📋 Government Organization:\n";
    echo "   ID: {$govOrg->id}\n";
    echo "   Name: {$govOrg->legal_name}\n";
    echo "   Wallet: {$govOrg->wallet_address}\n";
    echo "   Status: {$govOrg->verification_status}\n";
    echo "   Write Scopes: " . count($govOrg->write_scopes) . " scopes\n";
    echo "   Read Scopes: " . count($govOrg->read_scopes) . " scopes\n\n";
    
    // Update wallet address to match .env
    $envWallet = env('GOVERNMENT_WALLET_ADDRESS');
    if ($envWallet && $govOrg->wallet_address !== $envWallet) {
        echo "🔄 Updating wallet address to match .env...\n";
        $govOrg->update(['wallet_address' => $envWallet]);
        echo "✅ Wallet address updated to: {$envWallet}\n\n";
    }
    
    // Get test user
    $testUser = \App\Models\User::whereNotNull('did')->first();
    
    if (!$testUser) {
        echo "❌ No users found for testing!\n";
        exit(1);
    }
    
    echo "👤 Test User:\n";
    echo "   ID: {$testUser->id}\n";
    echo "   Name: {$testUser->name}\n";
    echo "   Phone: {$testUser->phone}\n";
    echo "   DID: {$testUser->did}\n";
    
    // Check existing VCs
    $existingVCs = $testUser->verifiableCredentials()->pluck('vc_type')->toArray();
    echo "   Existing VCs: " . (!empty($existingVCs) ? implode(', ', $existingVCs) : 'None') . "\n\n";
    
    // Test Aadhaar simulation
    echo "🎯 Testing Aadhaar Simulation...\n";
    
    $aadhaarService = app(\App\Services\AadhaarSimulationService::class);
    $result = $aadhaarService->simulateAadhaarVerification(
        $testUser,
        '123456789012', // Test Aadhaar number
        $testUser->name
    );
    
    if ($result['success']) {
        echo "✅ Aadhaar simulation successful!\n";
        echo "   Message: {$result['message']}\n";
        echo "   Credential ID: {$result['credential_id']}\n";
        echo "   Transaction Hash: {$result['transaction_hash']}\n\n";
        
        echo "📄 Generated Aadhaar Data:\n";
        $aadhaarData = $result['aadhaar_data'];
        echo "   Aadhaar Number: {$aadhaarData['aadhaar_number']}\n";
        echo "   Name: {$aadhaarData['name']}\n";
        echo "   Date of Birth: {$aadhaarData['date_of_birth']}\n";
        echo "   Gender: {$aadhaarData['gender']}\n";
        echo "   Address: {$aadhaarData['address']['house_number']} {$aadhaarData['address']['street']}, {$aadhaarData['address']['area']}, {$aadhaarData['address']['city']}, {$aadhaarData['address']['state']} - {$aadhaarData['address']['pincode']}\n";
        echo "   Issued Date: {$aadhaarData['issued_date']}\n";
        echo "   Valid Until: {$aadhaarData['valid_until']}\n";
        echo "   Issuing Authority: {$aadhaarData['issuing_authority']}\n";
        echo "   Simulation: " . ($aadhaarData['simulation'] ? 'Yes' : 'No') . "\n\n";
        
        // Check if VC was actually created in database
        $newVC = \App\Models\VerifiableCredential::where('subject_did', $testUser->did)
            ->where('vc_type', 'aadhaar_card')
            ->latest()
            ->first();
            
        if ($newVC) {
            echo "✅ Aadhaar VC confirmed in database:\n";
            echo "   VC ID: {$newVC->id}\n";
            echo "   Subject DID: {$newVC->subject_did}\n";
            echo "   VC Type: {$newVC->vc_type}\n";
            echo "   IPFS CID: {$newVC->ipfs_cid}\n";
            echo "   Transaction Hash: {$newVC->transaction_hash}\n";
            echo "   Created: {$newVC->created_at}\n";
        } else {
            echo "❌ Aadhaar VC not found in database!\n";
        }
        
    } else {
        echo "❌ Aadhaar simulation failed!\n";
        echo "   Error: {$result['message']}\n";
    }
    
    echo "\n🎉 Aadhaar simulation test completed!\n";
    echo "💡 Now users can register with Aadhaar numbers and get automatic Aadhaar VCs!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 