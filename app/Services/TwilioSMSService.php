<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class TwilioSMSService
{
    private $accountSid;
    private $authToken;
    private $fromNumber;

    public function __construct()
    {
        $this->accountSid = env('TWILIO_SID');
        $this->authToken = env('TWILIO_AUTH_TOKEN');
        $this->fromNumber = env('TWILIO_PHONE_NUMBER');
    }

    /**
     * Send a custom SMS message
     */
    public function sendSMS($to, $message)
    {
        try {
            Log::info('Sending SMS via Twilio', [
                'to' => $to,
                'from' => $this->fromNumber,
                'message_length' => strlen($message)
            ]);

            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json", [
                    'To' => $to,
                    'From' => $this->fromNumber,
                    'Body' => $message
                ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('SMS sent successfully via Twilio', [
                    'message_sid' => $data['sid'] ?? 'unknown',
                    'status' => $data['status'] ?? 'unknown'
                ]);
                return [
                    'success' => true,
                    'message_sid' => $data['sid'] ?? null,
                    'status' => $data['status'] ?? null
                ];
            } else {
                Log::error('Twilio SMS failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return [
                    'success' => false,
                    'error' => 'SMS delivery failed: ' . $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Twilio SMS exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'SMS service error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send VC issuance notification
     */
    public function sendVCIssuanceNotification($userPhone, $bankName, $credentialType, $issuedAt)
    {
        $message = "SarvOne: Your credential has been issued!\n\n";
        $message .= "Bank: {$bankName}\n";
        $message .= "Type: {$credentialType}\n";
        $message .= "Issued: " . date('d M Y, h:i A', strtotime($issuedAt)) . "\n\n";
        $message .= "Your credential is now available in your SarvOne dashboard.";

        return $this->sendSMS($userPhone, $message);
    }

    /**
     * Send VC revocation notification
     */
    public function sendVCRevocationNotification($userPhone, $bankName, $credentialType)
    {
        $message = "SarvOne: A credential has been revoked\n\n";
        $message .= "Bank: {$bankName}\n";
        $message .= "Type: {$credentialType}\n";
        $message .= "Revoked: " . date('d M Y, h:i A') . "\n\n";
        $message .= "Please check your SarvOne dashboard for details.";

        return $this->sendSMS($userPhone, $message);
    }

    /**
     * Send welcome message for new users
     */
    public function sendWelcomeMessage($userPhone, $userName)
    {
        $message = "Welcome to SarvOne, {$userName}!\n\n";
        $message .= "Your account has been successfully verified.\n";
        $message .= "You can now receive digital credentials from authorized organizations.\n\n";
        $message .= "Thank you for choosing SarvOne!";

        return $this->sendSMS($userPhone, $message);
    }

    /**
     * Send organization approval notification
     */
    public function sendOrgApprovalNotification($orgPhone, $orgName, $scopes)
    {
        $scopesList = implode(', ', $scopes);
        $message = "SarvOne: Organization Approved!\n\n";
        $message .= "Organization: {$orgName}\n";
        $message .= "Authorized Scopes: {$scopesList}\n";
        $message .= "Approved: " . date('d M Y, h:i A') . "\n\n";
        $message .= "You can now issue credentials to users.";

        return $this->sendSMS($orgPhone, $message);
    }
} 