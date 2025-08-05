<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Twilio SMS Debug Test ===\n\n";

// Test 1: Check environment variables
echo "1. Checking Twilio Environment Variables:\n";
$twilioSid = env('TWILIO_SID');
$twilioAuthToken = env('TWILIO_AUTH_TOKEN');
$twilioPhoneNumber = env('TWILIO_PHONE_NUMBER');

echo "   TWILIO_SID: " . ($twilioSid ? substr($twilioSid, 0, 10) . '...' : 'NOT SET') . "\n";
echo "   TWILIO_AUTH_TOKEN: " . ($twilioAuthToken ? substr($twilioAuthToken, 0, 10) . '...' : 'NOT SET') . "\n";
echo "   TWILIO_PHONE_NUMBER: " . ($twilioPhoneNumber ?: 'NOT SET') . "\n\n";

if (!$twilioSid || !$twilioAuthToken || !$twilioPhoneNumber) {
    echo "❌ ERROR: Missing Twilio environment variables!\n";
    exit(1);
}

// Test 2: Test basic SMS sending with verified number
echo "2. Testing Basic SMS Sending:\n";
$testPhone = '+919738603862'; // Verified number
$testMessage = "🧪 Test SMS from SarvOne - " . date('Y-m-d H:i:s');

try {
    echo "   Sending test SMS to: $testPhone\n";
    
    $response = Http::withBasicAuth($twilioSid, $twilioAuthToken)
        ->asForm()
        ->post("https://api.twilio.com/2010-04-01/Accounts/{$twilioSid}/Messages.json", [
            'To' => $testPhone,
            'From' => $twilioPhoneNumber,
            'Body' => $testMessage
        ]);

    echo "   Response Status: " . $response->status() . "\n";
    echo "   Response Body: " . $response->body() . "\n\n";

    if ($response->successful()) {
        $data = $response->json();
        echo "   ✅ SMS sent successfully!\n";
        echo "   Message SID: " . ($data['sid'] ?? 'unknown') . "\n";
        echo "   Status: " . ($data['status'] ?? 'unknown') . "\n\n";
    } else {
        echo "   ❌ SMS sending failed!\n";
        echo "   Error: " . $response->body() . "\n\n";
    }

} catch (Exception $e) {
    echo "   ❌ Exception: " . $e->getMessage() . "\n\n";
}

// Test 3: Test TwilioSMSService class with verified number
echo "3. Testing TwilioSMSService Class:\n";
try {
    $smsService = new \App\Services\TwilioSMSService();
    
    $result = $smsService->sendVCIssuanceNotification(
        $testPhone,
        'Test Bank',
        'Account Opening',
        now()
    );

    echo "   Service Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

    if ($result['success']) {
        echo "   ✅ TwilioSMSService working correctly!\n";
    } else {
        echo "   ❌ TwilioSMSService failed: " . $result['error'] . "\n";
    }

} catch (Exception $e) {
    echo "   ❌ TwilioSMSService Exception: " . $e->getMessage() . "\n";
}

// Test 4: Check if user has phone number
echo "\n4. Checking User Phone Numbers:\n";
try {
    $users = \App\Models\User::whereNotNull('phone')->get();
    echo "   Users with phone numbers: " . $users->count() . "\n";
    
    foreach ($users as $user) {
        echo "   - User ID: {$user->id}, Phone: {$user->phone}, DID: {$user->did}\n";
    }
    
    if ($users->count() == 0) {
        echo "   ⚠️  No users have phone numbers set!\n";
    }

} catch (Exception $e) {
    echo "   ❌ Error checking users: " . $e->getMessage() . "\n";
}

// Test 5: Check how OTP registration works
echo "\n5. Checking OTP Registration Process:\n";
try {
    // Look for OTP-related code
    $otpFiles = [
        'app/Http/Controllers/AuthController.php',
        'app/Http/Controllers/VerificationController.php',
        'app/Services/OTPService.php'
    ];
    
    foreach ($otpFiles as $file) {
        if (file_exists($file)) {
            echo "   Found OTP file: $file\n";
            $content = file_get_contents($file);
            if (strpos($content, 'TWILIO') !== false || strpos($content, 'SMS') !== false) {
                echo "   ✅ Contains Twilio/SMS code\n";
            }
        }
    }

} catch (Exception $e) {
    echo "   ❌ Error checking OTP files: " . $e->getMessage() . "\n";
}

echo "\n=== Debug Complete ===\n"; 