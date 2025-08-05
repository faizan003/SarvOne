<?php

require_once 'vendor/autoload.php';

use App\Models\GovernmentScheme;
use App\Models\User;
use App\Services\SchemeNotificationService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🎯 Creating test scheme for notification demonstration...\n";

try {
    // Create a scheme that matches the actual user credentials
    $scheme = GovernmentScheme::create([
        'scheme_name' => 'Income Certificate Holder Support Program',
        'description' => 'Special support program for citizens who have verified income certificates.',
        'category' => 'other',
        'max_income' => 1000000, // High income limit
        'min_age' => 18,
        'max_age' => 100,
        'required_credentials' => ['income_certificate'], // Match actual user credentials
        'caste_criteria' => null,
        'education_criteria' => null,
        'employment_criteria' => null,
        'benefit_amount' => 3000,
        'benefit_type' => 'grant',
        'application_deadline' => now()->addDays(30),
        'status' => 'active',
        'created_by' => 'Test System'
    ]);

    echo "✅ Test scheme created: {$scheme->scheme_name}\n";
    echo "📋 Scheme ID: {$scheme->id}\n\n";

    // Get current users
    $users = User::whereNotNull('did')->get();
    echo "👥 Found {$users->count()} users with DIDs:\n";
    
    foreach ($users as $user) {
        echo "   - User ID: {$user->id}, Phone: {$user->phone}, DID: {$user->did}\n";
        
        // Check user's VCs
        $userVCs = $user->verifiableCredentials()->pluck('vc_type')->toArray();
        echo "     VCs: " . implode(', ', $userVCs) . "\n";
    }

    echo "\n🔍 Testing notification system...\n";
    
    // Test the notification service
    $notificationService = app(SchemeNotificationService::class);
    $result = $notificationService->notifyEligibleUsersForNewScheme($scheme);
    
    echo "📊 Results:\n";
    echo "   Total users checked: {$result['total_users']}\n";
    echo "   Eligible users: {$result['eligible_users']}\n";
    echo "   Notifications sent: {$result['notified_users']}\n";

    if ($result['notified_users'] > 0) {
        echo "🎉 Success! Notifications were sent to eligible users!\n";
        
        // Show notification details
        echo "\n📱 Notification Details:\n";
        $notifications = \App\Models\SchemeNotification::where('scheme_id', $scheme->id)->get();
        foreach ($notifications as $notification) {
            echo "   - User ID: {$notification->user_id}\n";
            echo "     Message: " . substr($notification->message, 0, 100) . "...\n";
            echo "     SMS Sent: " . ($notification->sms_sent ? 'Yes' : 'No') . "\n";
            echo "     Status: {$notification->sms_status}\n\n";
        }
    } else {
        echo "⚠️ No eligible users found. This might be because:\n";
        echo "   - Users don't have the required credentials (income_certificate)\n";
        echo "   - Users don't meet age/income criteria\n";
        echo "   - Users were already notified for this scheme\n";
    }

    echo "\n💡 To test manually, run:\n";
    echo "   php artisan schemes:check-eligibility --scheme-id={$scheme->id}\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} 