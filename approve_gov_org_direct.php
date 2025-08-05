<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚀 Directly Approving Government Organization on Blockchain...\n\n";

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
    echo "   Blockchain Approved: " . ($govOrg->blockchain_approved ? 'Yes' : 'No') . "\n\n";
    
    // Check if already approved
    if ($govOrg->blockchain_approved) {
        echo "✅ Organization is already approved on blockchain!\n";
        echo "   Transaction Hash: {$govOrg->approval_transaction_hash}\n";
        exit(0);
    }
    
    echo "🔄 Approving organization on blockchain...\n";
    
    // Get admin controller
    $adminController = app(\App\Http\Controllers\AdminController::class);
    
    // Create a proper request object
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'remarks' => 'Government organization for Aadhaar simulation - Direct approval',
        'did_prefix' => 'government'
    ]);
    
    // Set the request method and headers
    $request->setMethod('POST');
    $request->headers->set('Content-Type', 'application/json');
    $request->headers->set('Accept', 'application/json');
    
    echo "📝 Sending approval request...\n";
    echo "   Remarks: {$request->remarks}\n";
    echo "   DID Prefix: {$request->did_prefix}\n\n";
    
    // Call the approveOrganization method
    $response = $adminController->approveOrganization($request, $govOrg->id);
    
    echo "📊 Approval Response:\n";
    echo "   Status Code: " . $response->getStatusCode() . "\n";
    echo "   Content: " . $response->getContent() . "\n\n";
    
    // Parse the JSON response
    $result = json_decode($response->getContent(), true);
    
    if ($result && isset($result['success']) && $result['success']) {
        echo "\n✅ Organization approved on blockchain successfully!\n";
        echo "   Transaction Hash: {$result['transaction_hash']}\n";
        
        // Refresh organization data
        $govOrg->refresh();
        echo "   Blockchain Approved: " . ($govOrg->blockchain_approved ? 'Yes' : 'No') . "\n";
        
        // Test Aadhaar VC issuance
        echo "\n🧪 Testing Aadhaar VC issuance after approval...\n";
        
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
                echo "   IPFS CID: {$result['ipfs_cid']}\n";
            } else {
                echo "❌ Aadhaar VC issuance failed: {$result['message']}\n";
            }
        }
        
    } else {
        echo "\n❌ Failed to approve organization: " . ($result['message'] ?? 'Unknown error') . "\n";
        
        if (isset($result['error'])) {
            echo "   Error Details: " . json_encode($result['error']) . "\n";
        }
    }
    
    echo "\n🎉 Direct blockchain approval process completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 