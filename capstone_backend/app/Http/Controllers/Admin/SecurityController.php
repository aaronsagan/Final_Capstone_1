<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\SecurityService;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    public function activityLogs(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        // Filter by user
        if ($userId = $request->query('user_id')) {
            $query->where('user_id', $userId);
        }

        // Filter by action
        if ($action = $request->query('action')) {
            $query->where('action', $action);
        }

        // Filter by date range
        if ($fromDate = $request->query('from_date')) {
            $query->where('created_at', '>=', $fromDate);
        }

        if ($toDate = $request->query('to_date')) {
            $query->where('created_at', '<=', $toDate);
        }

        return $query->paginate(50);
    }

    public function securitySummary()
    {
        $summary = [
            'total_activities_today' => ActivityLog::whereDate('created_at', today())->count(),
            'failed_logins_today' => ActivityLog::where('action', 'failed_login')
                ->whereDate('created_at', today())->count(),
            'suspicious_activities' => ActivityLog::where('action', 'like', '%suspicious%')
                ->orWhere('action', 'like', '%brute_force%')
                ->whereDate('created_at', today())->count(),
            'active_users_today' => ActivityLog::whereDate('created_at', today())
                ->distinct('user_id')->count(),
        ];

        return response()->json($summary);
    }
}
