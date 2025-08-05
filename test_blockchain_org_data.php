<?php
/**
 * Test script to verify the new get organization endpoint from blockchain
 */

echo "🔍 Testing Blockchain Organization Data Retrieval\n";
echo str_repeat("=", 50) . "\n\n";

$blockchain_url = 'http://localhost:8003';

// Test organization DID that we just approved
$test_org_did = 'did:sarvone:test:00001';

echo "Testing get organization endpoint...\n";
echo "DID: $test_org_did\n\n";

// Test the new endpoint
$get_org_url = $blockchain_url . '/get_org/' . urlencode($test_org_did);
echo "Calling: $get_org_url\n";

$start_time = microtime(true);
$response = @file_get_contents($get_org_url);
$end_time = microtime(true);
$response_time = ($end_time - $start_time) * 1000;

if ($response) {
    $data = json_decode($response, true);
    echo "✅ SUCCESS (Response Time: " . number_format($response_time, 2) . "ms)\n";
    echo "Response:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
    
    if ($data['success'] && $data['approved']) {
        echo "🎉 Organization is approved on blockchain!\n";
        echo "   - DID: " . $data['orgDID'] . "\n";
        echo "   - Address: " . $data['mainAddress'] . "\n";
        echo "   - Scopes: " . implode(', ', $data['scopes'] ?? []) . "\n";
    } elseif ($data['success'] && !$data['approved']) {
        echo "⚠️ Organization exists but is not approved\n";
    } else {
        echo "❌ Failed to retrieve organization data\n";
    }
} else {
    echo "❌ Failed to connect to blockchain service\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Test completed!\n";
?> 