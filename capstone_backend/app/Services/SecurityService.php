<?php

namespace App\Services;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class SecurityService
{
    /**
     * Log user activity
     */
    public function logActivity(User $user, string $action, array $details = [], string $ipAddress = null)
    {
        try {
            ActivityLog::create([
                'user_id' => $user->id,
                'user_role' => $user->role,
                'action' => $action,
                'details' => json_encode($details),
                'ip_address' => $ipAddress ?? Request::ip(),
                'user_agent' => Request::header('User-Agent'),
                'session_id' => session()->getId(),
            ]);

            // Also log to Laravel's log for backup
            Log::info("User Activity: {$user->role} - {$action}", [
                'user_id' => $user->id,
                'details' => $details,
                'ip' => $ipAddress ?? Request::ip(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to log user activity: ' . $e->getMessage());
        }
    }

    /**
     * Log authentication events
     */
    public function logAuthEvent(string $event, User $user = null, array $details = [])
    {
        $details['event_type'] = 'authentication';
        $details['timestamp'] = now()->toISOString();

        if ($user) {
            $this->logActivity($user, $event, $details);
        }

        Log::info("Auth Event: $event", $details);
    }

    /**
     * Log security events (suspicious activities)
     */
    public function logSecurityEvent(string $event, array $details = [], string $severity = 'medium')
    {
        $details['event_type'] = 'security';
        $details['severity'] = $severity;
        $details['timestamp'] = now()->toISOString();

        Log::warning("Security Event: $event", $details);

        // For high severity events, also send notifications
        if ($severity === 'high') {
            $this->notifyAdminsOfSecurityEvent($event, $details);
        }
    }

    /**
     * Log compliance events (document uploads, verification status changes)
     */
    public function logComplianceEvent(string $event, array $details = [])
    {
        $details['event_type'] = 'compliance';
        $details['timestamp'] = now()->toISOString();

        Log::info("Compliance Event: $event", $details);

        // Also create a compliance record for auditing
        $this->createComplianceRecord($event, $details);
    }

    /**
     * Check for suspicious login patterns
     */
    public function checkSuspiciousLogin(User $user, string $ipAddress)
    {
        $recentLogins = ActivityLog::where('user_id', $user->id)
            ->where('action', 'login')
            ->where('created_at', '>', now()->subHours(24))
            ->count();

        if ($recentLogins > 5) {
            $this->logSecurityEvent('multiple_login_attempts', [
                'user_id' => $user->id,
                'login_count_24h' => $recentLogins,
                'ip_address' => $ipAddress,
            ], 'high');
        }

        // Check for login from different locations
        $recentIPs = ActivityLog::where('user_id', $user->id)
            ->where('action', 'login')
            ->where('created_at', '>', now()->subDays(7))
            ->distinct('ip_address')
            ->count();

        if ($recentIPs > 3) {
            $this->logSecurityEvent('multiple_ip_logins', [
                'user_id' => $user->id,
                'unique_ips_7d' => $recentIPs,
                'current_ip' => $ipAddress,
            ], 'medium');
        }
    }

    /**
     * Monitor failed login attempts
     */
    public function logFailedLogin(string $email, string $ipAddress, string $reason = 'invalid_credentials')
    {
        $this->logSecurityEvent('failed_login', [
            'email' => $email,
            'ip_address' => $ipAddress,
            'reason' => $reason,
        ], 'low');

        // Check for brute force attempts
        $recentFailures = ActivityLog::where('details->email', $email)
            ->where('action', 'failed_login')
            ->where('created_at', '>', now()->subMinutes(15))
            ->count();

        if ($recentFailures >= 5) {
            $this->logSecurityEvent('brute_force_attempt', [
                'email' => $email,
                'failure_count' => $recentFailures,
                'ip_address' => $ipAddress,
            ], 'high');
        }
    }

    /**
     * Monitor data access patterns
     */
    public function logDataAccess(User $user, string $resource, string $action, $resourceId = null)
    {
        $details = [
            'resource_type' => $resource,
            'resource_id' => $resourceId,
            'access_action' => $action,
        ];

        // Check for excessive data access
        $recentAccess = ActivityLog::where('user_id', $user->id)
            ->where('details->resource_type', $resource)
            ->where('created_at', '>', now()->subMinutes(5))
            ->count();

        if ($recentAccess > 10) {
            $this->logSecurityEvent('excessive_data_access', [
                'user_id' => $user->id,
                'resource_type' => $resource,
                'access_count' => $recentAccess,
            ], 'medium');
        }

        $this->logActivity($user, "access_{$action}_{$resource}", $details);
    }

    /**
     * Monitor charity compliance status
     */
    public function checkComplianceStatus()
    {
        // Check for charities that haven't uploaded documents in 6 months
        $staleCharities = Charity::where('verification_status', 'approved')
            ->whereDoesntHave('documents', function($q) {
                $q->where('created_at', '>', now()->subMonths(6));
            })
            ->count();

        if ($staleCharities > 0) {
            $this->logComplianceEvent('stale_documentation', [
                'charities_without_recent_docs' => $staleCharities,
            ]);
        }

        // Check for charities with pending verifications older than 30 days
        $oldPending = Charity::where('verification_status', 'pending')
            ->where('created_at', '<', now()->subDays(30))
            ->count();

        if ($oldPending > 0) {
            $this->logComplianceEvent('old_pending_verifications', [
                'pending_over_30_days' => $oldPending,
            ]);
        }

        return [
            'stale_documentation' => $staleCharities,
            'old_pending_verifications' => $oldPending,
        ];
    }

    /**
     * Generate compliance report
     */
    public function generateComplianceReport()
    {
        $report = [
            'generated_at' => now()->toISOString(),
            'total_charities' => Charity::count(),
            'approved_charities' => Charity::where('verification_status', 'approved')->count(),
            'pending_charities' => Charity::where('verification_status', 'pending')->count(),
            'rejected_charities' => Charity::where('verification_status', 'rejected')->count(),
            'charities_with_documents' => Charity::has('documents')->count(),
            'total_documents' => CharityDocument::count(),
            'document_types' => CharityDocument::selectRaw('doc_type, COUNT(*) as count')
                ->groupBy('doc_type')
                ->get()
                ->pluck('count', 'doc_type'),
        ];

        $this->logComplianceEvent('compliance_report_generated', $report);

        return $report;
    }

    private function createComplianceRecord(string $event, array $details)
    {
        try {
            // Create a compliance record (you might want to create a ComplianceRecord model)
            // For now, we'll just log it
            Log::info("Compliance Record: $event", $details);
        } catch (\Exception $e) {
            Log::error('Failed to create compliance record: ' . $e->getMessage());
        }
    }

    private function notifyAdminsOfSecurityEvent(string $event, array $details)
    {
        try {
            $admins = User::where('role', 'admin')->get();

            foreach ($admins as $admin) {
                // You could integrate with the notification service here
                Log::warning("Security Alert for Admin {$admin->id}: $event", $details);
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify admins of security event: ' . $e->getMessage());
        }
    }
}
