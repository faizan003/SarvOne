<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Approving Government Organization on Blockchain (Simplified)\n";
echo "==========================================================\n\n";

// Government organization details
$govDid = 'did:sarvone:gov:government:001';
$govAddress = env('GOV_TEST_ADDRESS', '0xe1a204070F96072aC5A880434EbdB9534F5ff044');

// Essential government document scopes only (to avoid gas limit)
$essentialVcTypes = [
    // Government documents (main focus)
    'pan_card', 'aadhaar_card', 'income_certificate', 'voter_id', 'driving_license',
    'passport', 'birth_certificate', 'marriage_certificate', 'domicile_certificate',
    'caste_certificate', 'disability_certificate',
    
    // Basic verification scopes
    'address_proof', 'identity_proof', 'character_certificate',
    
    // Employment verification
    'employment_verification', 'salary_verification', 'experience_certificate',
    
    // Educational verification
    'degree_certificate', 'marksheet_verification', 'diploma_award',
    
    // Financial verification
    'bank_statement', 'salary_slip', 'financial_statement'
];

echo "Government DID: {$govDid}\n";
echo "Government Address: {$govAddress}\n";
echo "Total VC Types to Allow: " . count($essentialVcTypes) . "\n\n";

echo "Essential VC Types to be allowed:\n";
foreach ($essentialVcTypes as $type) {
    echo "- {$type}\n";
}
echo "\n";

try {
    echo "Calling blockchain service to approve government organization...\n";
    
    $response = Http::timeout(30)
        ->post(config('services.blockchain_service.url') . '/approve_org', [
            'orgDID' => $govDid,
            'orgAddress' => $govAddress,
            'scopes' => $essentialVcTypes
        ]);

    if ($response->successful()) {
        $result = $response->json();
        if ($result['success']) {
            echo "✅ Government organization approved successfully!\n";
            echo "Transaction Hash: {$result['tx_hash']}\n";
            echo "Explorer URL: {$result['explorer_url']}\n";
            echo "Block Number: {$result['block_number']}\n";
            echo "Gas Used: {$result['gas_used']}\n";
            
            // Verify the approval
            echo "\nVerifying approval on blockchain...\n";
            $verifyResponse = Http::timeout(30)
                ->get(config('services.blockchain_service.url') . '/get_org/' . urlencode($govDid));
            
            if ($verifyResponse->successful()) {
                $verifyResult = $verifyResponse->json();
                if ($verifyResult['success'] && $verifyResult['approved']) {
                    echo "✅ Verification successful! Government organization is approved on blockchain.\n";
                    echo "Approved Scopes: " . implode(', ', $verifyResult['scopes']) . "\n";
                } else {
                    echo "❌ Verification failed: Organization not approved or not found.\n";
                }
            } else {
                echo "❌ Verification request failed: " . $verifyResponse->body() . "\n";
            }
            
        } else {
            echo "❌ Approval failed: {$result['error']}\n";
        }
    } else {
        echo "❌ Blockchain service error: " . $response->body() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\nScript completed.\n"; 