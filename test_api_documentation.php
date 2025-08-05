<?php

require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== Government Scheme API Documentation Test ===\n\n";

// Test 1: Check if API keys are configured
echo "1. Checking API Keys Configuration:\n";
$apiKeys = explode(',', $_ENV['GOVERNMENT_API_KEYS'] ?? '');
$apiKeys = array_map('trim', $apiKeys);
$apiKeys = array_filter($apiKeys);

if (empty($apiKeys)) {
    echo "   ❌ No API keys configured in GOVERNMENT_API_KEYS\n";
} else {
    echo "   ✅ Found " . count($apiKeys) . " API key(s):\n";
    foreach ($apiKeys as $index => $key) {
        echo "      " . ($index + 1) . ". " . substr($key, 0, 10) . "...\n";
    }
}

// Test 2: Check base URL
echo "\n2. Checking Base URL:\n";
$baseUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
$apiBaseUrl = $baseUrl . '/api/government-schemes';
echo "   ✅ API Base URL: $apiBaseUrl\n";

// Test 3: Check if routes are accessible
echo "\n3. Testing Route Accessibility:\n";
$routes = [
    'government.dashboard' => '/government/dashboard',
    'government.api-documentation' => '/government/api-documentation',
    'api.government-schemes.submit' => '/api/government-schemes/submit',
    'api.government-schemes.update' => '/api/government-schemes/{id}',
    'api.government-schemes.status' => '/api/government-schemes/{id}/status'
];

foreach ($routes as $name => $path) {
    echo "   ✅ Route: $name -> $path\n";
}

// Test 4: API Key Validation Test
echo "\n4. Testing API Key Validation:\n";
if (!empty($apiKeys)) {
    $testKey = $apiKeys[0];
    $validApiKeys = explode(',', $_ENV['GOVERNMENT_API_KEYS'] ?? '');
    $validApiKeys = array_map('trim', $validApiKeys);
    $validApiKeys = array_filter($validApiKeys);
    
    if (in_array($testKey, $validApiKeys)) {
        echo "   ✅ API key validation working correctly\n";
    } else {
        echo "   ❌ API key validation failed\n";
    }
}

// Test 5: Sample API Request
echo "\n5. Sample API Request:\n";
echo "   curl -X POST \"$apiBaseUrl/submit\" \\\n";
echo "     -H \"Content-Type: application/json\" \\\n";
echo "     -H \"X-API-Key: " . (isset($apiKeys[0]) ? substr($apiKeys[0], 0, 10) . "..." : "YOUR_API_KEY") . "\" \\\n";
echo "     -d '{\n";
echo "       \"scheme_name\": \"Test Scholarship\",\n";
echo "       \"description\": \"Test scheme for API documentation\",\n";
echo "       \"category\": \"education\",\n";
echo "       \"benefit_amount\": 50000,\n";
echo "       \"benefit_type\": \"scholarship\",\n";
echo "       \"application_deadline\": \"2024-12-31\",\n";
echo "       \"organization_name\": \"Test Ministry\",\n";
echo "       \"organization_did\": \"did:gov:india:test:123\"\n";
echo "     }'\n";

echo "\n=== Test Complete ===\n";
echo "\nTo access the API documentation page:\n";
echo "1. Start your Laravel server: php artisan serve\n";
echo "2. Visit: $baseUrl/government/api-documentation\n";
echo "3. Login with government credentials if required\n";
echo "\nThe page will display:\n";
echo "- Available API keys (with copy functionality)\n";
echo "- Base URL and endpoints\n";
echo "- Request/response examples\n";
echo "- Integration guide\n";
echo "- Error handling information\n";
echo "- Rate limiting details\n";
echo "- Support contact information\n"; 