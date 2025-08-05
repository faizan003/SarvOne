<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Notification System ===\n\n";

// Test 1: Check Twilio SMS Service
echo "1. Testing Twilio SMS Service...\n";
try {
    $twilioService = new \App\Services\TwilioSMSService();
    
    // Test with a dummy phone number (should fail gracefully)
    $result = $twilioService->sendSMS('+1234567890', 'Test message from SarvOne');
    
    if ($result['success']) {
        echo "   ✅ SMS sent successfully\n";
        echo "   Message SID: " . ($result['message_sid'] ?? 'unknown') . "\n";
    } else {
        echo "   ❌ SMS failed: " . ($result['error'] ?? 'unknown error') . "\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Twilio service error: " . $e->getMessage() . "\n";
}

// Test 2: Check VC Issuance Notification
echo "\n2. Testing VC Issuance Notification...\n";
try {
    // Get a user with phone number
    $user = \App\Models\User::whereNotNull('phone')->first();
    
    if (!$user) {
        echo "   ❌ No user with phone number found\n";
    } else {
        echo "   👤 User: {$user->name} (Phone: {$user->phone})\n";
        
        // Get a recent VC
        $vc = \App\Models\VerifiableCredential::where('subject_did', $user->did)
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($vc) {
            echo "   📄 VC: {$vc->vc_type} (ID: {$vc->id})\n";
            
            // Test the notification method directly
            $credentialService = new \App\Services\CredentialService();
            
            // Use reflection to access private method
            $reflection = new ReflectionClass($credentialService);
            $notifyMethod = $reflection->getMethod('notifyUser');
            $notifyMethod->setAccessible(true);
            
            $notifyMethod->invoke($credentialService, $user, $vc->vc_type, $vc->id);
            echo "   ✅ Notification method executed successfully\n";
        } else {
            echo "   ❌ No VCs found for user\n";
        }
    }
} catch (\Exception $e) {
    echo "   ❌ VC notification error: " . $e->getMessage() . "\n";
}

// Test 3: Check Scheme Notification Service
echo "\n3. Testing Scheme Notification Service...\n";
try {
    $schemeNotificationService = new \App\Services\SchemeNotificationService(
        new \App\Services\TwilioSMSService()
    );
    
    // Get a user with phone number
    $user = \App\Models\User::whereNotNull('phone')->first();
    
    if (!$user) {
        echo "   ❌ No user with phone number found\n";
    } else {
        echo "   👤 User: {$user->name} (Phone: {$user->phone})\n";
        
        // Get a scheme
        $scheme = \App\Models\GovernmentScheme::where('status', 'active')->first();
        
        if ($scheme) {
            echo "   📋 Scheme: {$scheme->scheme_name}\n";
            
            // Check eligibility
            $eligibilityDetails = $scheme->getEligibilityDetails($user);
            echo "   🎯 Eligible: " . ($eligibilityDetails['eligible'] ? 'Yes' : 'No') . "\n";
            
            if ($eligibilityDetails['eligible']) {
                // Test notification
                $result = $schemeNotificationService->notifyEligibleUser($user, $scheme, $eligibilityDetails);
                
                if ($result['success']) {
                    echo "   ✅ Scheme notification sent successfully\n";
                    echo "   SMS Status: " . ($result['sms_sent'] ? 'Sent' : 'Failed') . "\n";
                } else {
                    echo "   ❌ Scheme notification failed: " . ($result['error'] ?? 'unknown error') . "\n";
                }
            } else {
                echo "   ⚠️ User not eligible for this scheme\n";
            }
        } else {
            echo "   ❌ No active schemes found\n";
        }
    }
} catch (\Exception $e) {
    echo "   ❌ Scheme notification error: " . $e->getMessage() . "\n";
}

// Test 4: Check Environment Configuration
echo "\n4. Testing Environment Configuration...\n";
echo "   TWILIO_SID: " . (env('TWILIO_SID') ? '✅ Set' : '❌ Not set') . "\n";
echo "   TWILIO_AUTH_TOKEN: " . (env('TWILIO_AUTH_TOKEN') ? '✅ Set' : '❌ Not set') . "\n";
echo "   TWILIO_PHONE_NUMBER: " . (env('TWILIO_PHONE_NUMBER') ? '✅ Set' : '❌ Not set') . "\n";
echo "   TWILIO_TEST_MODE: " . (env('TWILIO_TEST_MODE', 'false')) . "\n";

echo "\n=== Test Complete ===\n"; 