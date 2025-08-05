<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Marksheet VC Issuance ===\n\n";

// Test data - the actual marksheet data provided by user
$credentialData = [
    "student_id" => "STU_010",
    "student_name" => "Chota Bheem",
    "school_name" => "Dholakpur High School",
    "board" => "CBSE",
    "class" => "10th",
    "roll_number" => "DH123456",
    "exam_date" => "2024-03-15",
    "subjects" => [
        [
            "subject" => "Mathematics",
            "maximum_marks" => 100,
            "marks_obtained" => 95
        ],
        [
            "subject" => "Science",
            "maximum_marks" => 100,
            "marks_obtained" => 91
        ],
        [
            "subject" => "Social Science",
            "maximum_marks" => 100,
            "marks_obtained" => 88
        ],
        [
            "subject" => "English",
            "maximum_marks" => 100,
            "marks_obtained" => 90
        ],
        [
            "subject" => "Hindi",
            "maximum_marks" => 100,
            "marks_obtained" => 92
        ],
        [
            "subject" => "Information Technology",
            "maximum_marks" => 100,
            "marks_obtained" => 89
        ]
    ],
    "total_marks" => 600,
    "marks_obtained" => 545,
    "percentage" => 90.83,
    "grade" => "A+",
    "result" => "Pass"
];

echo "📋 Test Data:\n";
echo "   Student: {$credentialData['student_name']}\n";
echo "   School: {$credentialData['school_name']}\n";
echo "   Class: {$credentialData['class']}\n";
echo "   Percentage: {$credentialData['percentage']}%\n";
echo "   Grade: {$credentialData['grade']}\n\n";

// Get the organization (Dholakpur School)
$organization = \App\Models\Organization::where('legal_name', 'LIKE', '%Dholakpur%')
    ->orWhere('legal_name', 'LIKE', '%dholakpur%')
    ->first();

if (!$organization) {
    echo "❌ Dholakpur School organization not found!\n";
    exit(1);
}

echo "🏫 Organization Found:\n";
echo "   Name: {$organization->legal_name}\n";
echo "   Type: {$organization->organization_type}\n";
echo "   Status: {$organization->verification_status}\n";
echo "   DID: {$organization->did}\n";
echo "   Wallet: {$organization->wallet_address}\n\n";

// Get a verified user
$user = \App\Models\User::where('verification_status', 'verified')
    ->whereNotNull('verified_at')
    ->first();

if (!$user) {
    echo "❌ No verified user found!\n";
    exit(1);
}

echo "👤 User Found:\n";
echo "   Name: {$user->name}\n";
echo "   DID: {$user->did}\n";
echo "   Status: {$user->verification_status}\n\n";

// Test the VC issuance
try {
    echo "🚀 Issuing Marksheet VC...\n";
    
    $credentialService = new \App\Services\CredentialService();
    
    // Use the organization's private key
    $orgPrivateKey = 'd143f545d1cb2e055f77ca8a2102daaa90ab247237537ff1fa3b9759c696af3b';
    
    $result = $credentialService->issueCredential(
        $organization,
        $user,
        'marksheet',
        $credentialData,
        $orgPrivateKey
    );
    
    echo "✅ VC Issuance Successful!\n";
    echo "   VC ID: " . (is_array($result) ? json_encode($result) : $result) . "\n";
    
    // Get the created credential from database
    $credential = \App\Models\VerifiableCredential::where('vc_id', $result)->first();
    
    if ($credential) {
        echo "\n📄 Credential Details:\n";
        echo "   VC Type: {$credential->vc_type}\n";
        echo "   Subject: {$credential->subject_name}\n";
        echo "   Issuer: {$credential->issuer_name}\n";
        echo "   Status: {$credential->status}\n";
        echo "   Blockchain Verified: " . ($credential->blockchain_verified ? 'Yes' : 'No') . "\n";
        echo "   Transaction Hash: {$credential->blockchain_tx_hash}\n";
        echo "   IPFS Hash: {$credential->ipfs_hash}\n";
        echo "   Issued At: {$credential->issued_at}\n";
        echo "   Expires At: {$credential->expires_at}\n";
        
        if ($credential->blockchain_tx_hash) {
            echo "   Explorer URL: https://amoy.polygonscan.com/tx/{$credential->blockchain_tx_hash}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ VC Issuance Failed: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n"; 