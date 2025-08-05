<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 Setting up government credentials for Aadhaar simulation...\n\n";

try {
    // Check if government organization exists
    $govOrg = \App\Models\Organization::where('org_type', 'government')->first();
    
    if (!$govOrg) {
        echo "❌ Government organization not found!\n";
        echo "Please create a government organization first using the government approval page.\n";
        exit(1);
    }
    
    echo "✅ Government organization found:\n";
    echo "   ID: {$govOrg->id}\n";
    echo "   Name: {$govOrg->name}\n";
    echo "   Type: {$govOrg->org_type}\n";
    echo "   Wallet: {$govOrg->wallet_address}\n";
    echo "   Status: {$govOrg->status}\n\n";
    
    // Check if government credentials are set in .env
    $govPrivateKey = env('GOVERNMENT_PRIVATE_KEY');
    $govWalletAddress = env('GOVERNMENT_WALLET_ADDRESS');
    
    if (!$govPrivateKey || !$govWalletAddress) {
        echo "⚠️ Government credentials not found in .env file!\n";
        echo "Please add the following to your .env file:\n\n";
        echo "GOVERNMENT_PRIVATE_KEY={$govOrg->private_key}\n";
        echo "GOVERNMENT_WALLET_ADDRESS={$govOrg->wallet_address}\n\n";
        
        // Test with organization credentials
        echo "🔍 Testing with organization credentials...\n";
        
        // Test Aadhaar simulation
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
            } else {
                echo "❌ Aadhaar simulation test failed: {$result['message']}\n";
            }
        } else {
            echo "❌ No users found for testing!\n";
        }
        
    } else {
        echo "✅ Government credentials found in .env file!\n";
        echo "   Wallet: {$govWalletAddress}\n";
        echo "   Private Key: " . substr($govPrivateKey, 0, 10) . "...\n\n";
        
        // Test Aadhaar simulation
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
            } else {
                echo "❌ Aadhaar simulation test failed: {$result['message']}\n";
            }
        } else {
            echo "❌ No users found for testing!\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} 