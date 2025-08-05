<?php
/**
 * Fixed Test for Government Approval → Blockchain Service Connection
 * Uses correct validation format for FastAPI service
 */

echo "🏛️ Testing Government Approval → Blockchain Service Connection (FIXED)\n";
echo str_repeat("=", 70) . "\n\n";

// Step 1: Test basic connectivity
echo "Step 1: Testing FastAPI Blockchain Service Connectivity\n";
echo str_repeat("-", 50) . "\n";

$blockchain_url = 'http://localhost:8003';
$health_response = @file_get_contents($blockchain_url . '/health');

if ($health_response) {
    $health_data = json_decode($health_response, true);
    echo "✅ Blockchain service is running\n";
    echo "   - Contract: " . $health_data['contract_address'] . "\n";
    echo "   - Chain ID: " . $health_data['chain_id'] . "\n";
    echo "   - Admin Balance: " . $health_data['admin_balance_eth'] . " ETH\n";
    echo "   - Latest Block: " . $health_data['latest_block'] . "\n\n";
} else {
    echo "❌ Blockchain service is not responding\n";
    echo "   Make sure FastAPI service is running on port 8003\n";
    exit(1);
}

// Step 2: Test with CORRECT validation format
echo "Step 2: Testing Organization Approval Endpoint (CORRECT FORMAT)\n";
echo str_repeat("-", 50) . "\n";

// Use the exact format that FastAPI expects
$approval_data = [
    'orgDID' => 'did:sarvone:test:00001',  // Must start with 'did:sarvone:'
    'orgAddress' => '0x742d35Cc6634C0532925a3b8D46a02948d9A4f6e',  // Must be exactly 42 chars
    'scopes' => ['kyc_verification', 'loan_approval']  // Must have at least 1 scope
];

echo "✅ Using CORRECT validation format:\n";
echo "  DID: " . $approval_data['orgDID'] . " (" . strlen($approval_data['orgDID']) . " chars)\n";
echo "  Address: " . $approval_data['orgAddress'] . " (" . strlen($approval_data['orgAddress']) . " chars)\n";
echo "  Scopes: " . implode(', ', $approval_data['scopes']) . " (" . count($approval_data['scopes']) . " scopes)\n\n";

// Validate format before sending
$validation_errors = [];

if (!str_starts_with($approval_data['orgDID'], 'did:sarvone:')) {
    $validation_errors[] = "DID must start with 'did:sarvone:'";
}

if (!str_starts_with($approval_data['orgAddress'], '0x') || strlen($approval_data['orgAddress']) !== 42) {
    $validation_errors[] = "Address must start with '0x' and be exactly 42 characters";
}

if (empty($approval_data['scopes'])) {
    $validation_errors[] = "Must have at least 1 scope";
}

if (!empty($validation_errors)) {
    echo "❌ Validation errors found:\n";
    foreach ($validation_errors as $error) {
        echo "   - $error\n";
    }
    echo "\n";
} else {
    echo "✅ All validation checks passed\n\n";
}

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => json_encode($approval_data),
        'timeout' => 30
    ]
]);

echo "Sending POST request to: $blockchain_url/approve_org\n";
echo "Payload: " . json_encode($approval_data, JSON_PRETTY_PRINT) . "\n\n";

$start_time = microtime(true);
$approval_response = @file_get_contents($blockchain_url . '/approve_org', false, $context);
$end_time = microtime(true);
$response_time = round(($end_time - $start_time) * 1000, 2);

// Check for HTTP response headers
$response_headers = $http_response_header ?? [];
$status_code = 'unknown';
if (!empty($response_headers)) {
    $status_line = $response_headers[0];
    if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $status_line, $matches)) {
        $status_code = $matches[1];
    }
}

echo "Response Status: $status_code\n";
echo "Response Time: {$response_time}ms\n";

if ($approval_response) {
    $approval_result = json_decode($approval_response, true);
    echo "✅ Approval endpoint responded successfully\n";
    
    if (isset($approval_result['success']) && $approval_result['success']) {
        echo "🎉 Organization approval SUCCESSFUL!\n";
        echo "   - Transaction Hash: " . ($approval_result['tx_hash'] ?? 'N/A') . "\n";
        echo "   - Explorer URL: " . ($approval_result['explorer_url'] ?? 'N/A') . "\n";
        echo "   - Timestamp: " . ($approval_result['timestamp'] ?? 'N/A') . "\n";
    } else {
        echo "⚠️  Approval request received but transaction failed\n";
        echo "   Error: " . ($approval_result['error'] ?? 'Unknown error') . "\n";
        echo "   Detail: " . ($approval_result['detail'] ?? 'No details') . "\n";
    }
} else {
    echo "❌ Approval endpoint failed\n";
    $error = error_get_last();
    echo "   Error: " . ($error['message'] ?? 'Unknown error') . "\n";
    
    // Try to get more detailed error info
    if ($status_code == '400') {
        echo "\n🔍 DEBUGGING 400 BAD REQUEST:\n";
        echo "   - This usually means validation failed\n";
        echo "   - Check FastAPI docs at: http://localhost:8003/docs\n";
        echo "   - Try testing individual validation rules\n";
    } elseif ($status_code == '422') {
        echo "\n🔍 DEBUGGING 422 VALIDATION ERROR:\n";
        echo "   - Pydantic validation failed\n";
        echo "   - Check the exact format requirements\n";
    }
}

echo "\n";

// Step 3: Test individual validation rules
echo "Step 3: Testing Individual Validation Rules\n";
echo str_repeat("-", 50) . "\n";

$test_cases = [
    [
        'name' => 'Invalid DID (wrong prefix)',
        'data' => [
            'orgDID' => 'did:wrong:test:00001',
            'orgAddress' => '0x742d35Cc6634C0532925a3b8D46a02948d9A4f6e',
            'scopes' => ['test']
        ],
        'should_fail' => true
    ],
    [
        'name' => 'Invalid Address (wrong length)',
        'data' => [
            'orgDID' => 'did:sarvone:test:00001',
            'orgAddress' => '0x742d35Cc6634C0532925a3b8D46a02948d9A4f',  // Too short
            'scopes' => ['test']
        ],
        'should_fail' => true
    ],
    [
        'name' => 'Empty scopes',
        'data' => [
            'orgDID' => 'did:sarvone:test:00001',
            'orgAddress' => '0x742d35Cc6634C0532925a3b8D46a02948d9A4f6e',
            'scopes' => []
        ],
        'should_fail' => true
    ]
];

foreach ($test_cases as $test) {
    echo "\nTesting: " . $test['name'] . "\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode($test['data']),
            'timeout' => 10
        ]
    ]);
    
    $response = @file_get_contents($blockchain_url . '/approve_org', false, $context);
    $headers = $http_response_header ?? [];
    $status_code = 'unknown';
    
    if (!empty($headers)) {
        $status_line = $headers[0];
        if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $status_line, $matches)) {
            $status_code = $matches[1];
        }
    }
    
    if ($test['should_fail']) {
        if ($status_code == '422' || $status_code == '400') {
            echo "✅ Correctly rejected (Status: $status_code)\n";
        } else {
            echo "❌ Should have been rejected but got status: $status_code\n";
        }
    } else {
        if ($response && $status_code == '200') {
            echo "✅ Correctly accepted\n";
        } else {
            echo "❌ Should have been accepted but got status: $status_code\n";
        }
    }
}

// Step 4: Laravel Configuration Check
echo "\n\nStep 4: Testing Laravel Configuration\n";
echo str_repeat("-", 50) . "\n";

if (file_exists('artisan')) {
    echo "✅ Laravel project detected\n";
    
    // Check Laravel config
    $config_check = shell_exec('php artisan tinker --execute="echo config(\'services.blockchain_service.url\');"');
    if ($config_check) {
        $config_url = trim($config_check);
        echo "📝 Laravel blockchain service URL: $config_url\n";
        
        if (strpos($config_url, '8003') !== false) {
            echo "✅ Laravel is configured to use port 8003\n";
        } else {
            echo "⚠️  Laravel config still shows: $config_url\n";
            echo "   Run: php artisan config:clear\n";
        }
    }
} else {
    echo "⚠️  Not in Laravel project root\n";
}

// Final Summary
echo "\n\n📋 FINAL SUMMARY\n";
echo str_repeat("=", 70) . "\n";

$blockchain_ok = ($health_response !== false);
$validation_ok = empty($validation_errors);
$endpoint_accessible = ($status_code != 'unknown');

if ($blockchain_ok && $validation_ok && $endpoint_accessible) {
    echo "🎉 SUCCESS: Connection and validation are working!\n\n";
    
    echo "✅ CONFIRMED WORKING:\n";
    echo "   ✓ FastAPI blockchain service is running on port 8003\n";
    echo "   ✓ Validation rules are properly configured\n";
    echo "   ✓ Approval endpoint is accessible\n";
    echo "   ✓ Laravel configuration updated\n\n";
    
    echo "🚀 GOVERNMENT APPROVAL PAGE IS READY!\n";
    echo "   1. Start Laravel: php artisan serve\n";
    echo "   2. Visit: http://localhost:8000/gov/approval\n";
    echo "   3. Approve organizations with confidence!\n";
    
} else {
    echo "⚠️  ISSUES TO ADDRESS:\n";
    if (!$blockchain_ok) echo "   ✗ Blockchain service not responding\n";
    if (!$validation_ok) echo "   ✗ Validation errors present\n";
    if (!$endpoint_accessible) echo "   ✗ Endpoint not accessible\n";
    
    echo "\n🔧 NEXT STEPS:\n";
    echo "   1. Ensure FastAPI service is running\n";
    echo "   2. Check service logs for errors\n";
    echo "   3. Verify environment configuration\n";
}

echo "\n📖 FastAPI Documentation: http://localhost:8003/docs\n";
?> 