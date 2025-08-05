<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Checking Government Organization Blockchain Approval Status...\n\n";

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
    echo "   Wallet: {$govOrg->wallet_address}\n";
    echo "   Status: {$govOrg->verification_status}\n";
    echo "   Blockchain Approved: " . ($govOrg->blockchain_approved ? 'Yes' : 'No') . "\n";
    echo "   Approval Transaction: " . ($govOrg->approval_transaction_hash ?? 'None') . "\n\n";
    
    // Check if organization is approved on blockchain
    if (!$govOrg->blockchain_approved) {
        echo "⚠️ Organization is NOT approved on blockchain!\n";
        echo "🔄 Need to approve organization on blockchain first...\n\n";
        
        // Get admin controller to approve organization
        $adminController = app(\App\Http\Controllers\AdminController::class);
        
        try {
            echo "📝 Approving organization on blockchain...\n";
            
            // Create a mock request object
            $request = new \Illuminate\Http\Request();
            $request->merge([
                'remarks' => 'Government organization for Aadhaar simulation',
                'did_prefix' => 'government'
            ]);
            
            $result = $adminController->approveOrganization($request, $govOrg->id);
            
            if (isset($result['success']) && $result['success']) {
                echo "✅ Organization approved on blockchain successfully!\n";
                echo "   Transaction Hash: {$result['transaction_hash']}\n";
                
                // Refresh organization data
                $govOrg->refresh();
                echo "   Blockchain Approved: " . ($govOrg->blockchain_approved ? 'Yes' : 'No') . "\n";
            } else {
                echo "❌ Failed to approve organization: " . ($result['message'] ?? 'Unknown error') . "\n";
            }
        } catch (Exception $e) {
            echo "❌ Error approving organization: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✅ Organization is already approved on blockchain!\n";
    }
    
    echo "\n📝 Write Scopes (for issuing VCs):\n";
    foreach ($govOrg->write_scopes as $scope) {
        echo "   ✅ {$scope}\n";
    }
    
    echo "\n📖 Read Scopes (for verifying VCs):\n";
    foreach ($govOrg->read_scopes as $scope) {
        echo "   ✅ {$scope}\n";
    }
    
    // Check blockchain service for organization details
    echo "\n🔍 Checking blockchain for organization details...\n";
    
    try {
        $blockchainService = app(\App\Services\BlockchainService::class);
        
        // Get organization details from blockchain
        $blockchainOrg = $blockchainService->getOrganization($govOrg->wallet_address);
        
        if ($blockchainOrg) {
            echo "✅ Organization found on blockchain!\n";
            echo "   Name: {$blockchainOrg['name']}\n";
            echo "   Status: {$blockchainOrg['status']}\n";
            echo "   Write Scopes: " . count($blockchainOrg['writeScopes']) . " scopes\n";
            echo "   Read Scopes: " . count($blockchainOrg['readScopes']) . " scopes\n";
            
            echo "\n📝 Blockchain Write Scopes:\n";
            foreach ($blockchainOrg['writeScopes'] as $scope) {
                echo "   ✅ {$scope}\n";
            }
            
            echo "\n📖 Blockchain Read Scopes:\n";
            foreach ($blockchainOrg['readScopes'] as $scope) {
                echo "   ✅ {$scope}\n";
            }
            
            // Check if all required scopes are present
            $requiredWriteScopes = ['aadhaar_card', 'pan_card', 'voter_id', 'driving_license'];
            $missingWriteScopes = array_diff($requiredWriteScopes, $blockchainOrg['writeScopes']);
            
            if (!empty($missingWriteScopes)) {
                echo "\n⚠️ Missing Write Scopes on Blockchain:\n";
                foreach ($missingWriteScopes as $scope) {
                    echo "   ❌ {$scope}\n";
                }
            } else {
                echo "\n✅ All required write scopes are present on blockchain!\n";
            }
            
        } else {
            echo "❌ Organization not found on blockchain!\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error checking blockchain: " . $e->getMessage() . "\n";
    }
    
    // Test Aadhaar VC issuance
    echo "\n🧪 Testing Aadhaar VC issuance...\n";
    
    $testUser = \App\Models\User::whereNotNull('did')->first();
    
    if ($testUser) {
        echo "👤 Testing with user: {$testUser->name}\n";
        
        $aadhaarService = app(\App\Services\AadhaarSimulationService::class);
        $result = $aadhaarService->simulateAadhaarVerification(
            $testUser,
            '123456789012',
            $testUser->name
        );
        
        if ($result['success']) {
            echo "✅ Aadhaar VC issued successfully!\n";
            echo "   Credential ID: {$result['credential_id']}\n";
            echo "   Transaction Hash: {$result['transaction_hash']}\n";
        } else {
            echo "❌ Aadhaar VC issuance failed: {$result['message']}\n";
        }
    }
    
    echo "\n🎉 Blockchain approval check completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 