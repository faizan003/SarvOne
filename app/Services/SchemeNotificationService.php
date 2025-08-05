<?php

namespace App\Services;

use App\Models\User;
use App\Models\GovernmentScheme;
use App\Models\SchemeNotification;
use App\Services\TwilioSMSService;
use Illuminate\Support\Facades\Log;

class SchemeNotificationService
{
    protected $twilioService;

    public function __construct(TwilioSMSService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    /**
     * Check and notify eligible users for a new scheme
     */
    public function notifyEligibleUsersForNewScheme(GovernmentScheme $scheme)
    {
        Log::info('Checking eligible users for new scheme', [
            'scheme_id' => $scheme->id,
            'scheme_name' => $scheme->scheme_name
        ]);

        // Get all users with DIDs (active users)
        $users = User::whereNotNull('did')->get();
        
        $eligibleCount = 0;
        $notifiedCount = 0;

        foreach ($users as $user) {
            try {
                // Check if user is eligible
                if ($scheme->checkEligibility($user)) {
                    $eligibleCount++;
                    
                    // Check if already notified
                    if (!SchemeNotification::alreadyNotified($user->id, $scheme->id)) {
                        $this->sendSchemeNotification($user, $scheme);
                        $notifiedCount++;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error checking eligibility for user', [
                    'user_id' => $user->id,
                    'scheme_id' => $scheme->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Scheme notification completed', [
            'scheme_id' => $scheme->id,
            'total_users' => $users->count(),
            'eligible_users' => $eligibleCount,
            'notified_users' => $notifiedCount
        ]);

        return [
            'total_users' => $users->count(),
            'eligible_users' => $eligibleCount,
            'notified_users' => $notifiedCount
        ];
    }

    /**
     * Send notification to a specific user for a scheme
     */
    public function sendSchemeNotification(User $user, GovernmentScheme $scheme)
    {
        try {
            // Get eligibility details
            $eligibilityDetails = $scheme->getEligibilityDetails($user);
            
            // Create notification message
            $message = $this->createNotificationMessage($scheme, $eligibilityDetails);
            
            // Send SMS if user has phone number
            $smsSent = false;
            $smsStatus = null;
            
            if ($user->phone) {
                $smsSent = $this->sendSMSNotification($user->phone, $message);
                $smsStatus = $smsSent ? 'sent' : 'failed';
            }

            // Store notification in database
            SchemeNotification::create([
                'user_id' => $user->id,
                'scheme_id' => $scheme->id,
                'notification_type' => 'new_scheme',
                'message' => $message,
                'sent_at' => now(),
                'sms_sent' => $smsSent,
                'sms_status' => $smsStatus,
                'eligibility_details' => $eligibilityDetails
            ]);

            Log::info('Scheme notification sent', [
                'user_id' => $user->id,
                'user_phone' => $user->phone,
                'scheme_id' => $scheme->id,
                'sms_sent' => $smsSent
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error sending scheme notification', [
                'user_id' => $user->id,
                'scheme_id' => $scheme->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Create notification message for the scheme
     */
    protected function createNotificationMessage(GovernmentScheme $scheme, array $eligibilityDetails)
    {
        $benefitText = $scheme->benefit_amount 
            ? "₹" . number_format($scheme->benefit_amount) . " " . ucfirst($scheme->benefit_type)
            : ucfirst($scheme->benefit_type);

        $message = "🎯 New Government Scheme Alert!\n\n";
        $message .= "📋 {$scheme->scheme_name}\n";
        $message .= "💰 Benefit: {$benefitText}\n";
        $message .= "📅 Deadline: " . ($scheme->application_deadline ? $scheme->application_deadline->format('d/m/Y') : 'No deadline') . "\n\n";
        $message .= "✅ You are ELIGIBLE for this scheme!\n\n";
        $message .= "🔗 Apply now: " . url('/opportunity-hub') . "\n\n";
        $message .= "SarvOne - Your Digital Identity Partner";

        return $message;
    }

    /**
     * Send SMS notification
     */
    protected function sendSMSNotification($phoneNumber, $message)
    {
        try {
            // Check if Twilio credentials are configured
            if (!env('TWILIO_SID') || !env('TWILIO_AUTH_TOKEN') || !env('TWILIO_PHONE_NUMBER')) {
                Log::warning('Twilio credentials not configured, skipping SMS notification', [
                    'phone' => $phoneNumber
                ]);
                return false;
            }
            
            $response = $this->twilioService->sendSMS($phoneNumber, $message);
            return $response['success'] ?? false;
        } catch (\Exception $e) {
            Log::error('SMS notification failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check all users for new schemes (run periodically)
     */
    public function checkAllUsersForNewSchemes()
    {
        // Get schemes created in the last 24 hours
        $recentSchemes = GovernmentScheme::where('status', 'active')
            ->where('created_at', '>=', now()->subDay())
            ->get();

        $totalNotified = 0;

        foreach ($recentSchemes as $scheme) {
            $result = $this->notifyEligibleUsersForNewScheme($scheme);
            $totalNotified += $result['notified_users'];
        }

        Log::info('Periodic scheme check completed', [
            'recent_schemes' => $recentSchemes->count(),
            'total_notified' => $totalNotified
        ]);

        return $totalNotified;
    }
} 