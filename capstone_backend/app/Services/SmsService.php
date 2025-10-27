<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SmsService
{
    protected $smsProvider;
    protected $apiKey;
    protected $senderId;

    public function __construct()
    {
        $this->smsProvider = config('sms.provider', 'twilio'); // twilio, nexmo, etc.
        $this->apiKey = config('sms.api_key');
        $this->senderId = config('sms.sender_id', 'DonationApp');
    }

    /**
     * Send SMS notification to a user
     */
    public function sendSms(User $user, string $message, string $type = 'general')
    {
        // Check if user has SMS notifications enabled and a phone number
        if (!$user->phone || !$user->sms_notifications_enabled) {
            Log::info("SMS not sent to {$user->name}: Phone not set or SMS disabled");
            return false;
        }

        // Check if user wants this type of notification
        if ($user->sms_notification_types && !in_array($type, $user->sms_notification_types)) {
            Log::info("SMS not sent to {$user->name}: Type '{$type}' not enabled");
            return false;
        }

        try {
            switch ($this->smsProvider) {
                case 'twilio':
                    return $this->sendViaTwilio($user->phone, $message);
                case 'nexmo':
                    return $this->sendViaNexmo($user->phone, $message);
                case 'mock':
                    return $this->sendViaMock($user, $message);
                default:
                    Log::warning("Unsupported SMS provider: {$this->smsProvider}");
                    return false;
            }
        } catch (\Exception $e) {
            Log::error("Failed to send SMS to {$user->phone}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS via Twilio
     */
    private function sendViaTwilio(string $phone, string $message)
    {
        if (!$this->apiKey) {
            Log::warning('Twilio API key not configured');
            return false;
        }

        $response = Http::withBasicAuth(config('sms.twilio_sid'), $this->apiKey)
            ->post('https://api.twilio.com/2010-04-01/Accounts/' . config('sms.twilio_sid') . '/Messages.json', [
                'From' => config('sms.twilio_number'),
                'To' => $phone,
                'Body' => $message
            ]);

        if ($response->successful()) {
            Log::info("SMS sent successfully via Twilio to {$phone}");
            return true;
        }

        Log::error("Twilio SMS failed: " . $response->body());
        return false;
    }

    /**
     * Send SMS via Nexmo/Vonage
     */
    private function sendViaNexmo(string $phone, string $message)
    {
        if (!$this->apiKey) {
            Log::warning('Nexmo API key not configured');
            return false;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':' . config('sms.nexmo_secret'))
        ])->post('https://rest.nexmo.com/sms/json', [
            'from' => $this->senderId,
            'to' => $phone,
            'text' => $message
        ]);

        if ($response->successful()) {
            Log::info("SMS sent successfully via Nexmo to {$phone}");
            return true;
        }

        Log::error("Nexmo SMS failed: " . $response->body());
        return false;
    }

    /**
     * Mock SMS service for development/testing
     */
    private function sendViaMock(User $user, string $message)
    {
        Log::info("MOCK SMS would be sent to {$user->phone}: {$message}");
        return true;
    }

    /**
     * Send donation confirmation SMS
     */
    public function sendDonationConfirmation($donation)
    {
        $user = $donation->donor;

        $message = "Thank you for your donation of ₱{$donation->amount} to {$donation->charity->name}. Your receipt number is {$donation->receipt_no}.";

        return $this->sendSms($user, $message, 'donation_updates');
    }

    /**
     * Send charity post update SMS
     */
    public function sendCharityPostUpdate($user, $post)
    {
        $message = "New update from {$post->charity->name}: {$post->title}";

        return $this->sendSms($user, $message, 'charity_posts');
    }
}
