<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActionLog;
use Illuminate\Http\Request;

class AdminActionLogController extends Controller
{
    /**
     * Get all admin action logs with filters
     */
    public function index(Request $request)
    {
        $query = AdminActionLog::with('admin:id,name,email');

        // Filter by admin
        if ($request->has('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        // Filter by action type
        if ($request->has('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        // Filter by target type
        if ($request->has('target_type')) {
            $query->where('target_type', $request->target_type);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Search in notes
        if ($request->has('search')) {
            $query->where('notes', 'like', '%' . $request->search . '%');
        }

        // Order by most recent first
        $query->orderBy('created_at', 'desc');

        $logs = $query->paginate(50);

        return response()->json($logs);
    }

    /**
     * Get statistics for admin actions
     */
    public function statistics()
    {
        return response()->json([
            'total_actions' => AdminActionLog::count(),
            'actions_today' => AdminActionLog::whereDate('created_at', today())->count(),
            'actions_this_week' => AdminActionLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'actions_this_month' => AdminActionLog::whereMonth('created_at', now()->month)->count(),
            'by_action_type' => AdminActionLog::selectRaw('action_type, COUNT(*) as count')
                ->groupBy('action_type')
                ->get(),
            'by_admin' => AdminActionLog::selectRaw('admin_id, COUNT(*) as count')
                ->with('admin:id,name')
                ->groupBy('admin_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'recent_actions' => AdminActionLog::with('admin:id,name')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ]);
    }

    /**
     * Export logs to CSV
     */
    public function export(Request $request)
    {
        $query = AdminActionLog::with('admin:id,name,email');

        // Apply same filters as index
        if ($request->has('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }
        if ($request->has('action_type')) {
            $query->where('action_type', $request->action_type);
        }
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'admin_logs_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, ['ID', 'Admin', 'Action Type', 'Target Type', 'Target ID', 'Notes', 'IP Address', 'Date/Time']);
            
            // CSV Data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->admin->name ?? 'N/A',
                    $log->action_type,
                    $log->target_type ?? 'N/A',
                    $log->target_id ?? 'N/A',
                    $log->notes ?? '',
                    $log->ip_address ?? 'N/A',
                    $log->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
