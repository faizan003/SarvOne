<?php
/**
 * Test script to verify connection between Laravel and FastAPI blockchain service
 */

// Test configuration for port 8003
$blockchain_service_urls = [
    'http://localhost:8001',  // Current Laravel config default
    'http://localhost:8003',  // Actual FastAPI service port
];

echo "🔍 Testing connection to FastAPI Blockchain Service...\n\n";

foreach ($blockchain_service_urls as $index => $url) {
    echo "Test " . ($index + 1) . ": Testing connection to $url\n";
    echo str_repeat("-", 50) . "\n";
    
    // Test health check endpoint
    $health_url = $url . '/health';
    echo "Testing health endpoint: $health_url\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'header' => 'Content-Type: application/json'
        ]
    ]);
    
    $start_time = microtime(true);
    $response = @file_get_contents($health_url, false, $context);
    $end_time = microtime(true);
    $response_time = round(($end_time - $start_time) * 1000, 2);
    
    if ($response !== false) {
        echo "✅ SUCCESS - Response time: {$response_time}ms\n";
        echo "Response: $response\n";
        
        // Test contract info endpoint
        $contract_url = $url . '/contract/info';
        echo "\nTesting contract info endpoint: $contract_url\n";
        
        $contract_response = @file_get_contents($contract_url, false, $context);
        if ($contract_response !== false) {
            echo "✅ Contract info endpoint working\n";
            $contract_data = json_decode($contract_response, true);
            if ($contract_data) {
                echo "Contract Address: " . ($contract_data['contract_address'] ?? 'N/A') . "\n";
                echo "Chain ID: " . ($contract_data['chain_id'] ?? 'N/A') . "\n";
            }
        } else {
            echo "❌ Contract info endpoint failed\n";
        }
        
        echo "\n🎯 RECOMMENDATION: Use $url for BLOCKCHAIN_SERVICE_URL\n";
        break;
    } else {
        echo "❌ FAILED - Service not responding\n";
        echo "Error: " . error_get_last()['message'] . "\n";
    }
    
    echo "\n";
}

echo "\n📝 Configuration Instructions:\n";
echo "1. Add to your Laravel .env file:\n";
echo "   BLOCKCHAIN_SERVICE_URL=http://localhost:8003\n";
echo "   BLOCKCHAIN_SERVICE_TIMEOUT=30\n\n";

echo "2. Or update config/services.php default value:\n";
echo "   'url' => env('BLOCKCHAIN_SERVICE_URL', 'http://localhost:8003'),\n\n";

echo "3. Clear Laravel config cache:\n";
echo "   php artisan config:clear\n";
echo "   php artisan config:cache\n\n";

// Test approval endpoint simulation
echo "🧪 Testing approval endpoint simulation:\n";
echo str_repeat("-", 50) . "\n";

$approval_url = 'http://localhost:8003/approve_org';
$test_data = json_encode([
    'orgDID' => 'did:sarvone:test:00001',
    'orgAddress' => '0x742d35Cc6634C0532925a3b8D46a02948d9A4f6e',
    'scopes' => ['kyc_verification']
]);

echo "POST $approval_url\n";
echo "Data: $test_data\n";

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $test_data,
        'timeout' => 30
    ]
]);

$approval_response = @file_get_contents($approval_url, false, $context);
if ($approval_response !== false) {
    echo "✅ Approval endpoint is accessible\n";
    echo "Response: $approval_response\n";
} else {
    echo "❌ Approval endpoint test failed\n";
    echo "Note: This is expected if admin private key is not configured\n";
}

echo "\n🔗 FastAPI Documentation: http://localhost:8003/docs\n";
?> 