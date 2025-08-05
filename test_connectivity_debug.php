<?php
/**
 * Debug connectivity between Laravel and FastAPI service
 */

echo "🔍 CONNECTIVITY DEBUGGING\n";
echo str_repeat("=", 40) . "\n\n";

$urls = [
    'http://localhost:8003/health',
    'http://127.0.0.1:8003/health',
    'http://0.0.0.0:8003/health'
];

foreach ($urls as $url) {
    echo "Testing: $url\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 5, // Shorter timeout for testing
            'ignore_errors' => true
        ]
    ]);
    
    $start = microtime(true);
    $response = @file_get_contents($url, false, $context);
    $time = (microtime(true) - $start) * 1000;
    
    if ($response !== false) {
        echo "✅ SUCCESS - Response time: " . number_format($time, 2) . "ms\n";
        echo "Response: " . substr($response, 0, 100) . "...\n";
    } else {
        echo "❌ FAILED - Timeout after " . number_format($time, 2) . "ms\n";
        $error = error_get_last();
        if ($error) {
            echo "Error: " . $error['message'] . "\n";
        }
    }
    echo "\n";
}

// Test with cURL
echo "Testing with cURL (more detailed):\n";
echo str_repeat("-", 30) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8003/health');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_STDERR, fopen('php://temp', 'w+'));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
$info = curl_getinfo($ch);

if ($response !== false && $httpCode === 200) {
    echo "✅ cURL SUCCESS\n";
    echo "HTTP Code: $httpCode\n";
    echo "Connect Time: " . ($info['connect_time'] * 1000) . "ms\n";
    echo "Total Time: " . ($info['total_time'] * 1000) . "ms\n";
} else {
    echo "❌ cURL FAILED\n";
    echo "HTTP Code: $httpCode\n";
    echo "Error: $error\n";
    echo "Connect Time: " . ($info['connect_time'] * 1000) . "ms\n";
    echo "Total Time: " . ($info['total_time'] * 1000) . "ms\n";
}

curl_close($ch);

echo "\n🔧 POSSIBLE ISSUES:\n";
echo "1. Windows Firewall blocking connections\n";
echo "2. Antivirus software blocking localhost\n";
echo "3. FastAPI not binding to correct interface\n";
echo "4. Port 8003 conflicting with other service\n";
echo "5. Laravel HTTP client configuration issue\n";

echo "\n💡 SOLUTIONS TO TRY:\n";
echo "1. Restart FastAPI with: uvicorn main:app --host 127.0.0.1 --port 8003\n";
echo "2. Try different port: uvicorn main:app --host 127.0.0.1 --port 8004\n";
echo "3. Check Windows Firewall settings\n";
echo "4. Test from different terminal/browser\n";

?> 