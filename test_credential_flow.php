<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\CredentialService;
use App\Models\User;
use App\Models\Organization;

try {
    echo "Testing Credential Issuance Flow...\n";
    
    // Get the organization and user
    $organization = Organization::first();
    $user = User::where('did', 'did:sarvone:d0460b16ef8cfe9f')->first();
    
    if (!$organization) {
        echo "No organization found\n";
        exit(1);
    }
    
    if (!$user) {
        echo "User not found\n";
        exit(1);
    }
    
    echo "Organization: " . $organization->legal_name . " (DID: " . $organization->did . ")\n";
    echo "User: " . $user->name . " (DID: " . $user->did . ")\n";
    
    // Test credential data
    $credentialData = [
        'account_type' => 'savings',
        'account_number' => '1234567890',
        'ifsc_code' => 'SBIN0000123',
        'branch_name' => 'Main Branch',
        'opening_date' => '2025-07-31'
    ];
    
    $orgPrivateKey = 'f2290aa94f68f8391fbe2b5cca6a886bcd4984cbf16c2840e734dafa5ffe0e87';
    
    echo "Starting credential issuance...\n";
    
    // Create credential service and issue credential
    $credentialService = new CredentialService();
    $result = $credentialService->issueCredential(
        $organization,
        $user,
        'account_opening',
        $credentialData,
        $orgPrivateKey
    );
    
    echo "Credential issuance result:\n";
    print_r($result);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 