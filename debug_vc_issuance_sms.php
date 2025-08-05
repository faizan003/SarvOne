<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Organization;
use App\Services\TwilioSMSService;

echo "🔍 VC Issuance SMS Debug\n";
echo "========================\n\n";

// Test phone number
$testPhone = '+919738603862';

echo "📱 Test Phone: $testPhone\n\n";

try {
    // Get the most recent VC from database
    $latestVC = \DB::table('verifiable_credentials')
        ->orderBy('created_at', 'desc')
        ->first();
    
    if (!$latestVC) {
        echo "❌ No VCs found in database\n";
        exit(1);
    }
    
    echo "📋 Latest VC Found:\n";
    echo "   ID: " . $latestVC->id . "\n";
    echo "   Type: " . $latestVC->credential_type . "\n";
    echo "   Issuer DID: " . $latestVC->issuer_did . "\n";
    echo "   Recipient DID: " . $latestVC->recipient_did . "\n";
    echo "   Created: " . $latestVC->created_at . "\n\n";
    
    // Get user details
    $user = User::find($latestVC->recipient_user_id);
    if (!$user) {
        echo "❌ User not found for ID: " . $latestVC->recipient_user_id . "\n";
        exit(1);
    }
    
    echo "👤 User Details:\n";
    echo "   Name: " . $user->name . "\n";
    echo "   Phone: " . ($user->phone ?? 'NOT SET') . "\n";
    echo "   DID: " . $user->did . "\n\n";
    
    // Get organization details
    $organization = Organization::find($latestVC->issuer_organization_id);
    if (!$organization) {
        echo "❌ Organization not found for ID: " . $latestVC->issuer_organization_id . "\n";
        exit(1);
    }
    
    echo "🏢 Organization Details:\n";
    echo "   Name: " . $organization->name . "\n";
    echo "   Legal Name: " . $organization->legal_name . "\n";
    echo "   DID: " . $organization->did . "\n\n";
    
    // Check if SMS should be sent
    echo "🔍 SMS Conditions Check:\n";
    echo "   User has phone: " . ($user->phone ? 'YES' : 'NO') . "\n";
    echo "   Organization found: " . ($organization ? 'YES' : 'NO') . "\n";
    echo "   Organization name available: " . (($organization->name || $organization->legal_name) ? 'YES' : 'NO') . "\n\n";
    
    if (!$user->phone) {
        echo "❌ SMS cannot be sent - user has no phone number\n";
        exit(1);
    }
    
    if (!$organization) {
        echo "❌ SMS cannot be sent - organization not found\n";
        exit(1);
    }
    
    // Test SMS sending
    echo "📤 Testing SMS Sending:\n";
    $smsService = app(TwilioSMSService::class);
    
    // Use legal_name if available, otherwise use name
    $bankName = $organization->legal_name ?? $organization->name ?? 'Unknown Bank';
    $issuedAt = $latestVC->created_at ?? now();
    
    echo "   Bank Name: $bankName\n";
    echo "   Credential Type: " . $latestVC->credential_type . "\n";
    echo "   Issued At: $issuedAt\n";
    echo "   User Phone: " . $user->phone . "\n\n";
    
    $smsResult = $smsService->sendVCIssuanceNotification(
        $user->phone,
        $bankName,
        $latestVC->credential_type,
        $issuedAt
    );
    
    if ($smsResult['success']) {
        echo "✅ SMS sent successfully!\n";
        echo "   Message SID: " . ($smsResult['message_sid'] ?? 'N/A') . "\n";
        echo "   Status: " . ($smsResult['status'] ?? 'N/A') . "\n";
    } else {
        echo "❌ SMS failed: " . ($smsResult['error'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n🔧 Potential Issues:\n";
    echo "==================\n";
    echo "1. User phone number not set in database\n";
    echo "2. Organization name/legal_name not set\n";
    echo "3. Exception in notifyUser method\n";
    echo "4. Twilio configuration issues\n";
    
    echo "\n📝 Recent Logs Check:\n";
    echo "===================\n";
    echo "Check these log files for errors:\n";
    echo "- storage/logs/laravel.log\n";
    echo "- storage/logs/twilio_debug.log (if exists)\n";
    
} catch (Exception $e) {
    echo "❌ Debug failed with exception: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

?> 