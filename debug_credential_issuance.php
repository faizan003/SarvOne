<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

echo "Debugging Credential Issuance...\n";

// Test data for different credential types
$testCases = [
    'bank_account' => [
        'recipient_did' => 'did:sarvone:cabe1e55c9d1cd18',
        'credential_type' => 'account_opening',
        'credential_data' => [
            'account_type' => 'Savings',
            'bank_name' => 'SBI',
            'account_number' => '1234567890',
            'opening_date' => '2024-01-15'
        ],
        'org_private_key' => '96d6b0550dfb5bc50350187f586dd74bb5e0fc999440f16ef6b6599703bc9b1f'
    ],
    'college_marksheet' => [
        'recipient_did' => 'did:sarvone:cabe1e55c9d1cd18',
        'credential_type' => 'marksheet_university',
        'credential_data' => [
            'university_name' => 'GM Institute of Technology',
            'course_name' => 'Computer Science',
            'semester' => '6th',
            'total_marks' => '850',
            'obtained_marks' => '720',
            'percentage' => '84.7',
            'grade' => 'A',
            'academic_year' => '2023-24'
        ],
        'org_private_key' => '96d6b0550dfb5bc50350187f586dd74bb5e0fc999440f16ef6b6599703bc9b1f'
    ]
];

foreach ($testCases as $testName => $testData) {
    echo "\n=== Testing $testName ===\n";
    echo "Credential Type: " . $testData['credential_type'] . "\n";
    echo "Data: " . json_encode($testData['credential_data'], JSON_PRETTY_PRINT) . "\n";
    
    try {
        // Test the API endpoint directly
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post('http://127.0.0.1:8000/organization/api/issue-credential', $testData);
        
        echo "Status Code: " . $response->status() . "\n";
        echo "Response Headers: " . json_encode($response->headers()) . "\n";
        echo "Response Body: " . $response->body() . "\n";
        
        if ($response->successful()) {
            echo "✅ $testName: SUCCESS\n";
        } else {
            echo "❌ $testName: FAILED\n";
        }
        
    } catch (Exception $e) {
        echo "❌ $testName: EXCEPTION - " . $e->getMessage() . "\n";
    }
}

echo "\nDone!\n"; 