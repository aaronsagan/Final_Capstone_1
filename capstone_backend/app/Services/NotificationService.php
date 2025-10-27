<?php

namespace App\Services;

use App\Models\User;
use App\Models\Donation;
use App\Models\Charity;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send donation confirmation notification
     */
    public function sendDonationConfirmation(Donation $donation)
    {
        if ($donation->is_anonymous || !$donation->donor) {
            return; // Don't send to anonymous donors
        }

        try {
            $data = [
                'donor_name' => $donation->donor->name,
                'charity_name' => $donation->charity->name,
                'amount' => number_format($donation->amount, 2),
                'purpose' => ucfirst($donation->purpose),
                'receipt_no' => $donation->receipt_no,
                'donation_date' => $donation->donated_at->format('F j, Y'),
            ];

            // Send email notification
            Mail::send('emails.donation-confirmation', $data, function($message) use ($donation) {
                $message->to($donation->donor->email)
                        ->subject('Donation Confirmation - ' . $donation->charity->name);
            });

            Log::info("Donation confirmation sent to {$donation->donor->email}");

        } catch (\Exception $e) {
            Log::error('Failed to send donation confirmation: ' . $e->getMessage());
        }
    }

    /**
     * Send donation received notification to charity
     */
    public function sendDonationReceived(Charity $charity, Donation $donation)
    {
        try {
            $data = [
                'charity_name' => $charity->name,
                'donor_name' => $donation->is_anonymous ? 'Anonymous Donor' : $donation->donor?->name,
                'amount' => number_format($donation->amount, 2),
                'purpose' => ucfirst($donation->purpose),
                'donation_date' => $donation->donated_at->format('F j, Y \a\t g:i A'),
            ];

            // Send email to charity admin
            if ($charity->owner) {
                Mail::send('emails.donation-received', $data, function($message) use ($charity) {
                    $message->to($charity->owner->email)
                            ->subject('New Donation Received - ' . $charity->name);
                });

                Log::info("Donation received notification sent to {$charity->owner->email}");
            }

        } catch (\Exception $e) {
            Log::error('Failed to send donation received notification: ' . $e->getMessage());
        }
    }

    /**
     * Send charity verification status notification
     */
    public function sendVerificationStatus(Charity $charity, string $status)
    {
        try {
            $data = [
                'charity_name' => $charity->name,
                'status' => $status,
                'admin_name' => 'System Administrator',
                'rejection_reason' => $charity->rejection_reason ?? null,
            ];

            // Send email to charity admin
            if ($charity->owner) {
                $template = $status === 'approved' ? 'emails.verification-approved' : 'emails.verification-rejected';

                Mail::send($template, $data, function($message) use ($charity, $status) {
                    $message->to($charity->owner->email)
                            ->subject("Charity {$status} - " . $charity->name);
                });

                Log::info("Verification {$status} notification sent to {$charity->owner->email}");
            }

        } catch (\Exception $e) {
            Log::error('Failed to send verification notification: ' . $e->getMessage());
        }
    }

    /**
     * Send campaign update notification
     */
    public function sendCampaignUpdate($campaign, $updateType = 'created')
    {
        try {
            $data = [
                'campaign_title' => $campaign->title,
                'charity_name' => $campaign->charity->name,
                'target_amount' => number_format($campaign->target_amount, 2),
                'description' => $campaign->description,
                'update_type' => $updateType,
            ];

            // Get all donors who donated to this charity
            $donors = User::whereHas('donations', function($q) use ($campaign) {
                $q->where('charity_id', $campaign->charity_id);
            })->where('role', 'donor')->get();

            foreach ($donors as $donor) {
                if ($donor->email) {
                    Mail::send('emails.campaign-update', $data, function($message) use ($donor, $campaign) {
                        $message->to($donor->email)
                                ->subject("Campaign Update - {$campaign->title}");
                    });
                }
            }

            Log::info("Campaign update notification sent to {$donors->count()} donors");

        } catch (\Exception $e) {
            Log::error('Failed to send campaign update notification: ' . $e->getMessage());
        }
    }

    /**
     * Send fund usage update notification
     */
    public function sendFundUsageUpdate($fundUsage)
    {
        try {
            $data = [
                'charity_name' => $fundUsage->charity->name,
                'amount' => number_format($fundUsage->amount, 2),
                'category' => ucfirst($fundUsage->category),
                'description' => $fundUsage->description,
                'usage_date' => $fundUsage->created_at->format('F j, Y'),
            ];

            // Get donors who contributed to this charity
            $donors = User::whereHas('donations', function($q) use ($fundUsage) {
                $q->where('charity_id', $fundUsage->charity_id);
            })->where('role', 'donor')->get();

            foreach ($donors as $donor) {
                if ($donor->email) {
                    Mail::send('emails.fund-usage-update', $data, function($message) use ($donor, $fundUsage) {
                        $message->to($donor->email)
                                ->subject("Fund Usage Update - {$fundUsage->charity->name}");
                    });
                }
            }

            Log::info("Fund usage update notification sent to {$donors->count()} donors");

        } catch (\Exception $e) {
            Log::error('Failed to send fund usage update notification: ' . $e->getMessage());
        }
    }

    /**
     * Send system alert notification
     */
    public function sendSystemAlert($user, $message, $type = 'info')
    {
        try {
            $data = [
                'user_name' => $user->name,
                'message' => $message,
                'type' => $type,
            ];

            Mail::send('emails.system-alert', $data, function($message) use ($user, $type) {
                $message->to($user->email)
                        ->subject('System Notification');
            });

            Log::info("System alert sent to {$user->email}");

        } catch (\Exception $e) {
            Log::error('Failed to send system alert: ' . $e->getMessage());
        }
    }
}
