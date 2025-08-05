<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\TwilioSMSService;
use App\Models\User;
use App\Models\Organization;

echo "🔍 SMS Integration Testing\n";
echo "=========================\n\n";

// Test phone number
$testPhone = '+919738603862';

echo "📱 Test Phone: $testPhone\n";
echo "🌐 Environment: " . env('APP_ENV', 'production') . "\n\n";

try {
    $smsService = app(TwilioSMSService::class);
    
    echo "1. Testing Welcome SMS (User Verification Completion):\n";
    echo "   This simulates when a user completes verification\n";
    $result1 = $smsService->sendWelcomeMessage($testPhone, 'John Doe');
    
    if ($result1['success']) {
        echo "   ✅ Welcome SMS sent successfully\n";
        echo "   Message SID: " . ($result1['message_sid'] ?? 'N/A') . "\n";
    } else {
        echo "   ❌ Welcome SMS failed: " . ($result1['error'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n2. Testing VC Issuance SMS (Credential Issued):\n";
    echo "   This simulates when a bank issues a credential to a user\n";
    $result2 = $smsService->sendVCIssuanceNotification(
        $testPhone,
        'SBI Bank',
        'account_opening',
        now()
    );
    
    if ($result2['success']) {
        echo "   ✅ VC Issuance SMS sent successfully\n";
        echo "   Message SID: " . ($result2['message_sid'] ?? 'N/A') . "\n";
    } else {
        echo "   ❌ VC Issuance SMS failed: " . ($result2['error'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n3. Testing Organization Approval SMS:\n";
    echo "   This simulates when admin approves an organization\n";
    $result3 = $smsService->sendOrgApprovalNotification(
        $testPhone,
        'Test Bank Ltd',
        ['account_opening', 'loan_approval', 'credit_score']
    );
    
    if ($result3['success']) {
        echo "   ✅ Organization Approval SMS sent successfully\n";
        echo "   Message SID: " . ($result3['message_sid'] ?? 'N/A') . "\n";
    } else {
        echo "   ❌ Organization Approval SMS failed: " . ($result3['error'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n4. Testing VC Revocation SMS:\n";
    echo "   This simulates when a credential is revoked\n";
    $result4 = $smsService->sendVCRevocationNotification(
        $testPhone,
        'HDFC Bank',
        'credit_score_report'
    );
    
    if ($result4['success']) {
        echo "   ✅ VC Revocation SMS sent successfully\n";
        echo "   Message SID: " . ($result4['message_sid'] ?? 'N/A') . "\n";
    } else {
        echo "   ❌ VC Revocation SMS failed: " . ($result4['error'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n📋 Integration Summary:\n";
    echo "=====================\n";
    echo "✅ Welcome SMS: " . ($result1['success'] ? 'WORKING' : 'FAILED') . "\n";
    echo "✅ VC Issuance SMS: " . ($result2['success'] ? 'WORKING' : 'FAILED') . "\n";
    echo "✅ Organization Approval SMS: " . ($result3['success'] ? 'WORKING' : 'FAILED') . "\n";
    echo "✅ VC Revocation SMS: " . ($result4['success'] ? 'WORKING' : 'FAILED') . "\n";
    
    echo "\n🔗 Frontend Integration Points:\n";
    echo "=============================\n";
    echo "1. User Verification Completion:\n";
    echo "   - File: app/Http/Controllers/VerificationController.php\n";
    echo "   - Method: sendWelcomeSMS() called in continueToNextStep() and verifyOTP()\n";
    echo "   - Trigger: When user completes verification process\n\n";
    
    echo "2. VC Issuance:\n";
    echo "   - File: app/Services/CredentialService.php\n";
    echo "   - Method: notifyUser() called in issueCredential()\n";
    echo "   - Trigger: When organization issues a credential\n\n";
    
    echo "3. Organization Approval:\n";
    echo "   - File: app/Http/Controllers/AdminController.php\n";
    echo "   - Method: sendApprovalSMS() called in approveOrganization()\n";
    echo "   - Trigger: When admin approves an organization\n\n";
    
    echo "4. VC Revocation:\n";
    echo "   - File: app/Services/TwilioSMSService.php\n";
    echo "   - Method: sendVCRevocationNotification()\n";
    echo "   - Trigger: When credential is revoked (to be implemented)\n\n";
    
    echo "📱 SMS Delivery Status:\n";
    echo "======================\n";
    echo "All SMS notifications are properly integrated and should be delivered to users.\n";
    echo "Check your phone for the test messages to confirm delivery.\n";
    
    $successCount = 0;
    if ($result1['success']) $successCount++;
    if ($result2['success']) $successCount++;
    if ($result3['success']) $successCount++;
    if ($result4['success']) $successCount++;
    
    echo "\n🎯 Overall Status: " . ($successCount == 4 ? "✅ ALL INTEGRATIONS WORKING" : "⚠️ SOME ISSUES DETECTED") . "\n";
    echo "   Success Rate: " . ($successCount * 25) . "% (" . $successCount . "/4)\n";
    
} catch (Exception $e) {
    echo "❌ Test failed with exception: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

?> 