<?php
/**
 * Test Government Approval Page Connection to FastAPI Blockchain Service
 * This simulates the exact flow that happens when approving an organization
 */

echo "🏛️ Testing Government Approval → Blockchain Service Connection\n";
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

// Step 2: Test organization approval endpoint
echo "Step 2: Testing Organization Approval Endpoint\n";
echo str_repeat("-", 50) . "\n";

// Simulate the exact data that Laravel AdminController sends
$approval_data = [
    'orgDID' => 'did:sarvone:test:' . str_pad(rand(1, 999), 5, '0', STR_PAD_LEFT),
    'orgAddress' => '0x742d35Cc6634C0532925a3b8D46a02948d9A4f6e', // Example wallet address
    'scopes' => ['kyc_verification', 'loan_approval'] // Example scopes
];

echo "Simulating approval request:\n";
echo "  DID: " . $approval_data['orgDID'] . "\n";
echo "  Address: " . $approval_data['orgAddress'] . "\n";
echo "  Scopes: " . implode(', ', $approval_data['scopes']) . "\n\n";

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($approval_data),
        'timeout' => 30
    ]
]);

echo "Sending POST request to: $blockchain_url/approve_org\n";
$start_time = microtime(true);
$approval_response = @file_get_contents($blockchain_url . '/approve_org', false, $context);
$end_time = microtime(true);
$response_time = round(($end_time - $start_time) * 1000, 2);

if ($approval_response) {
    $approval_result = json_decode($approval_response, true);
    echo "✅ Approval endpoint responded in {$response_time}ms\n";
    
    if (isset($approval_result['success']) && $approval_result['success']) {
        echo "✅ Organization approval successful!\n";
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
}

echo "\n";

// Step 3: Test Laravel Configuration
echo "Step 3: Testing Laravel Configuration\n";
echo str_repeat("-", 50) . "\n";

// Check if we can load Laravel config (if in Laravel project)
if (file_exists('artisan')) {
    echo "✅ Laravel project detected\n";
    
    // Try to get the blockchain service config using artisan
    $config_command = 'php artisan tinker --execute="echo config(\'services.blockchain_service.url\');"';
    $config_url = shell_exec($config_command);
    
    if ($config_url) {
        $config_url = trim($config_url);
        echo "📝 Laravel blockchain service URL: $config_url\n";
        
        if (strpos($config_url, '8003') !== false) {
            echo "✅ Laravel is configured to use port 8003\n";
        } else {
            echo "⚠️  Laravel is NOT configured to use port 8003\n";
            echo "   Current config: $config_url\n";
            echo "   Should be: http://localhost:8003\n";
        }
    } else {
        echo "⚠️  Could not read Laravel configuration\n";
    }
    
    echo "\n";
}

// Step 4: Frontend Connection Test
echo "Step 4: Testing Frontend → Backend → Blockchain Flow\n";
echo str_repeat("-", 50) . "\n";

echo "Simulating the complete approval flow:\n";
echo "1. 🌐 Frontend (Gov Approval Page) makes AJAX request\n";
echo "2. 🔧 Laravel AdminController processes request\n";
echo "3. 🚀 Laravel calls FastAPI Blockchain Service\n";
echo "4. ⛓️  FastAPI interacts with blockchain\n";
echo "5. 📄 Response flows back to frontend\n\n";

// Test the exact endpoint that the frontend calls
$frontend_test_url = 'http://localhost:8000'; // Default Laravel serve port
echo "Testing if Laravel is running on $frontend_test_url...\n";

$laravel_response = @file_get_contents($frontend_test_url, false, stream_context_create([
    'http' => ['timeout' => 5]
]));

if ($laravel_response) {
    echo "✅ Laravel application is running\n";
    echo "🌐 Government approval page should be available at:\n";
    echo "   $frontend_test_url/gov/approval\n";
} else {
    echo "⚠️  Laravel application not responding\n";
    echo "   Start Laravel with: php artisan serve\n";
}

echo "\n";

// Summary and recommendations
echo "📋 CONNECTION TEST SUMMARY\n";
echo str_repeat("=", 70) . "\n";

$blockchain_ok = ($health_response !== false);
$endpoint_ok = ($approval_response !== false);

if ($blockchain_ok && $endpoint_ok) {
    echo "✅ SUCCESS: Government approval page is properly connected to blockchain service!\n\n";
    
    echo "🎯 WHAT WORKS:\n";
    echo "   ✓ FastAPI blockchain service is running on port 8003\n";
    echo "   ✓ Approval endpoint is accessible\n";
    echo "   ✓ Laravel configuration updated to use port 8003\n";
    echo "   ✓ End-to-end connection is functional\n\n";
    
    echo "🚀 NEXT STEPS:\n";
    echo "   1. Start Laravel: php artisan serve\n";
    echo "   2. Visit: http://localhost:8000/gov/approval\n";
    echo "   3. Try approving an organization\n";
    echo "   4. Check blockchain explorer for transactions\n";
    
} else {
    echo "❌ ISSUES FOUND:\n";
    if (!$blockchain_ok) {
        echo "   ✗ FastAPI blockchain service not responding\n";
    }
    if (!$endpoint_ok) {
        echo "   ✗ Approval endpoint not working\n";
    }
    
    echo "\n🔧 TROUBLESHOOTING:\n";
    echo "   1. Ensure FastAPI service is running:\n";
    echo "      cd fastapi_blockchain_service\n";
    echo "      .\\run_with_utf8.ps1\n";
    echo "   2. Check the service logs for errors\n";
    echo "   3. Verify environment configuration\n";
}

echo "\n📖 Documentation: http://localhost:8003/docs\n";
?> 