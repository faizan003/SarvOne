<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\TwilioSMSService;

echo "Testing SMS Notifications\n";
echo "========================\n\n";

// Test phone number (replace with your actual test number)
$testPhone = '+919738603862'; // Replace with your test number

try {
    $smsService = app(TwilioSMSService::class);
    
    echo "1. Testing VC Issuance Notification...\n";
    $result1 = $smsService->sendVCIssuanceNotification(
        $testPhone,
        'SBI Bank',
        'account_opening',
        now()
    );
    
    if ($result1['success']) {
        echo "✅ VC Issuance SMS sent successfully\n";
        echo "   Message SID: " . ($result1['message_sid'] ?? 'N/A') . "\n";
    } else {
        echo "❌ VC Issuance SMS failed: " . ($result1['error'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n2. Testing Welcome Message...\n";
    $result2 = $smsService->sendWelcomeMessage($testPhone, 'John Doe');
    
    if ($result2['success']) {
        echo "✅ Welcome SMS sent successfully\n";
        echo "   Message SID: " . ($result2['message_sid'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Welcome SMS failed: " . ($result2['error'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n3. Testing Organization Approval Notification...\n";
    $result3 = $smsService->sendOrgApprovalNotification(
        $testPhone,
        'Test Bank Ltd',
        ['account_opening', 'loan_approval', 'credit_score']
    );
    
    if ($result3['success']) {
        echo "✅ Organization Approval SMS sent successfully\n";
        echo "   Message SID: " . ($result3['message_sid'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Organization Approval SMS failed: " . ($result3['error'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n4. Testing VC Revocation Notification...\n";
    $result4 = $smsService->sendVCRevocationNotification(
        $testPhone,
        'HDFC Bank',
        'credit_score_report'
    );
    
    if ($result4['success']) {
        echo "✅ VC Revocation SMS sent successfully\n";
        echo "   Message SID: " . ($result4['message_sid'] ?? 'N/A') . "\n";
    } else {
        echo "❌ VC Revocation SMS failed: " . ($result4['error'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n📱 All SMS tests completed!\n";
    echo "Check your phone for the test messages.\n";
    
} catch (Exception $e) {
    echo "❌ Test failed with exception: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

?> 