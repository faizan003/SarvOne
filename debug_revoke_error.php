<?php

require_once 'vendor/autoload.php';

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Debug Revoke Error ===\n\n";

try {
    // Test 1: Check if the VC exists
    $vcId = 'vc_6890faad37be0'; // The VC ID from the error
    echo "Testing VC ID: {$vcId}\n";
    
    $vc = \App\Models\VerifiableCredential::where('vc_id', $vcId)->first();
    
    if (!$vc) {
        echo "❌ VC not found in database\n";
        exit(1);
    }
    
    echo "✅ VC found in database\n";
    echo "   Subject DID: {$vc->subject_did}\n";
    echo "   Credential Hash: {$vc->credential_hash}\n";
    echo "   Status: {$vc->status}\n";
    echo "   Issuer Organization ID: {$vc->issuer_organization_id}\n";
    
    // Test 2: Check if organization exists and is verified
    $organization = \App\Models\Organization::find($vc->issuer_organization_id);
    
    if (!$organization) {
        echo "❌ Issuing organization not found\n";
        exit(1);
    }
    
    echo "✅ Organization found\n";
    echo "   Name: {$organization->legal_name}\n";
    echo "   Verification Status: {$organization->verification_status}\n";
    echo "   DID: {$organization->did}\n";
    
    // Test 3: Test blockchain service initialization
    echo "\n=== Testing Blockchain Service ===\n";
    
    $blockchainService = new \App\Services\BlockchainService();
    
    // Check if blockchain service is properly initialized
    $networkInfo = $blockchainService->getNetworkInfo();
    echo "Network Info:\n";
    print_r($networkInfo);
    
    // Test 4: Test the revoke method directly
    echo "\n=== Testing Revoke Method ===\n";
    
    try {
        $revokeResult = $blockchainService->revokeVC($vc->subject_did, $vc->credential_hash);
        echo "Revoke Result:\n";
        print_r($revokeResult);
    } catch (Exception $e) {
        echo "❌ Revoke method failed: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
    // Test 5: Check if the issue is with the controller method
    echo "\n=== Testing Controller Method ===\n";
    
    // Create a mock request
    $request = new \Illuminate\Http\Request();
    $request->merge(['revocation_reason' => 'Test revocation']);
    
    // Create controller instance
    $controller = new \App\Http\Controllers\OrganizationController();
    
    // Mock authentication
    \Illuminate\Support\Facades\Auth::shouldReceive('guard')
        ->with('organization')
        ->andReturnSelf();
    
    \Illuminate\Support\Facades\Auth::shouldReceive('user')
        ->andReturn($organization);
    
    try {
        $result = $controller->revokeVC($request, $vcId);
        echo "Controller Result:\n";
        print_r($result);
    } catch (Exception $e) {
        echo "❌ Controller method failed: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Debug failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Debug Complete ===\n"; 