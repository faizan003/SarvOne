<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\TwilioSMSService;

echo "🔍 SMS Delivery Debugging\n";
echo "========================\n\n";

// Test phone number
$testPhone = '+919738603862';

echo "📱 Test Phone: $testPhone\n";
echo "🌐 Environment: " . env('APP_ENV', 'production') . "\n\n";

// Check Twilio Configuration
echo "1. Checking Twilio Configuration:\n";
echo "   TWILIO_SID: " . (env('TWILIO_SID') ? substr(env('TWILIO_SID'), 0, 10) . '...' : 'NOT SET') . "\n";
echo "   TWILIO_AUTH_TOKEN: " . (env('TWILIO_AUTH_TOKEN') ? substr(env('TWILIO_AUTH_TOKEN'), 0, 10) . '...' : 'NOT SET') . "\n";
echo "   TWILIO_PHONE_NUMBER: " . (env('TWILIO_PHONE_NUMBER') ? env('TWILIO_PHONE_NUMBER') : 'NOT SET') . "\n";
echo "   TWILIO_TEST_MODE: " . (env('TWILIO_TEST_MODE') ? 'ENABLED' : 'DISABLED') . "\n\n";

// Test direct Twilio API call
echo "2. Testing Direct Twilio API Call:\n";

try {
    $sid = env('TWILIO_SID');
    $token = env('TWILIO_AUTH_TOKEN');
    $fromNumber = env('TWILIO_PHONE_NUMBER');
    
    if (!$sid || !$token || !$fromNumber) {
        echo "   ❌ Missing Twilio configuration\n";
        exit(1);
    }
    
    // Test message
    $testMessage = "🔍 SarvOne SMS Test\n\nThis is a test message to verify SMS delivery.\nTimestamp: " . date('Y-m-d H:i:s') . "\n\nIf you receive this, SMS is working!";
    
    echo "   📤 Sending test message...\n";
    echo "   From: $fromNumber\n";
    echo "   To: $testPhone\n";
    echo "   Message Length: " . strlen($testMessage) . " characters\n\n";
    
    // Make direct API call
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'To' => $testPhone,
        'From' => $fromNumber,
        'Body' => $testMessage
    ]));
    curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "   HTTP Status Code: $httpCode\n";
    
    if ($error) {
        echo "   ❌ cURL Error: $error\n";
    } else {
        $data = json_decode($response, true);
        
        if ($httpCode === 201) {
            echo "   ✅ Message sent successfully!\n";
            echo "   Message SID: " . ($data['sid'] ?? 'N/A') . "\n";
            echo "   Status: " . ($data['status'] ?? 'N/A') . "\n";
            echo "   Error Code: " . ($data['error_code'] ?? 'None') . "\n";
            echo "   Error Message: " . ($data['error_message'] ?? 'None') . "\n";
            
            // Check message status after a few seconds
            echo "\n3. Checking Message Status:\n";
            sleep(3);
            
            $statusCh = curl_init();
            curl_setopt($statusCh, CURLOPT_URL, "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages/{$data['sid']}.json");
            curl_setopt($statusCh, CURLOPT_USERPWD, "$sid:$token");
            curl_setopt($statusCh, CURLOPT_RETURNTRANSFER, true);
            
            $statusResponse = curl_exec($statusCh);
            $statusHttpCode = curl_getinfo($statusCh, CURLINFO_HTTP_CODE);
            curl_close($statusCh);
            
            if ($statusHttpCode === 200) {
                $statusData = json_decode($statusResponse, true);
                echo "   Current Status: " . ($statusData['status'] ?? 'Unknown') . "\n";
                echo "   Direction: " . ($statusData['direction'] ?? 'Unknown') . "\n";
                echo "   Price: " . ($statusData['price'] ?? 'N/A') . "\n";
                echo "   Price Unit: " . ($statusData['price_unit'] ?? 'N/A') . "\n";
                
                if (isset($statusData['error_code'])) {
                    echo "   Error Code: " . $statusData['error_code'] . "\n";
                    echo "   Error Message: " . $statusData['error_message'] . "\n";
                }
            } else {
                echo "   ❌ Failed to check message status\n";
            }
            
        } else {
            echo "   ❌ Failed to send message\n";
            echo "   Response: $response\n";
        }
    }
    
} catch (Exception $e) {
    echo "   ❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n4. Common Issues to Check:\n";
echo "   • Is the phone number in correct international format? (+91...)\n";
echo "   • Is the phone number verified in Twilio console (for trial accounts)?\n";
echo "   • Is the Twilio account active and not suspended?\n";
echo "   • Are there sufficient credits in the Twilio account?\n";
echo "   • Is the 'from' number verified and approved?\n";
echo "   • Are there any regional restrictions?\n";

echo "\n5. Next Steps:\n";
echo "   • Check Twilio Console for message logs\n";
echo "   • Verify phone number in Twilio Console\n";
echo "   • Check account status and credits\n";
echo "   • Try with a different phone number\n";

?> 