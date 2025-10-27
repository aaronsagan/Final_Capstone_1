<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\AdminActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    /**
     * Submit a new report (Donor or Charity)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reported_entity_type' => 'required|in:user,charity,campaign,donation',
            'reported_entity_id' => 'required|integer',
            'reason' => 'required|in:fraud,fake_proof,inappropriate_content,scam,fake_charity,misuse_of_funds,spam,harassment,other',
            'description' => 'required|string|min:10|max:1000',
            'evidence' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $evidencePath = null;

        // Handle evidence file upload
        if ($request->hasFile('evidence')) {
            $evidencePath = $request->file('evidence')->store('reports/evidence', 'public');
        }

        $report = Report::create([
            'reporter_id' => $user->id,
            'reporter_role' => $user->role,
            'reported_entity_type' => $request->reported_entity_type,
            'reported_entity_id' => $request->reported_entity_id,
            'reason' => $request->reason,
            'description' => $request->description,
            'evidence_path' => $evidencePath,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Report submitted successfully. Our team will review it shortly.',
            'report' => $report->load('reporter'),
        ], 201);
    }

    /**
     * Get current user's submitted reports
     */
    public function myReports(Request $request)
    {
        $user = $request->user();
        
        $reports = Report::where('reporter_id', $user->id)
            ->with(['reviewer'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($reports);
    }

    /**
     * Get all reports (Admin only)
     */
    public function index(Request $request)
    {
        $query = Report::with(['reporter', 'reviewer']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by entity type
        if ($request->has('entity_type')) {
            $query->where('reported_entity_type', $request->entity_type);
        }

        // Filter by reason
        if ($request->has('reason')) {
            $query->where('reason', $request->reason);
        }

        // Search by description
        if ($request->has('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        // Order by most recent first
        $query->orderBy('created_at', 'desc');

        $reports = $query->paginate(20);

        return response()->json($reports);
    }

    /**
     * Get single report details (Admin only)
     */
    public function show(Report $report)
    {
        $report->load(['reporter', 'reviewer']);

        // Load the reported entity based on type
        $entityData = null;
        switch ($report->reported_entity_type) {
            case 'user':
                $entityData = \App\Models\User::find($report->reported_entity_id);
                break;
            case 'charity':
                $entityData = \App\Models\Charity::with('owner')->find($report->reported_entity_id);
                break;
            case 'campaign':
                $entityData = \App\Models\Campaign::with('charity')->find($report->reported_entity_id);
                break;
            case 'donation':
                $entityData = \App\Models\Donation::with(['donor', 'charity'])->find($report->reported_entity_id);
                break;
        }

        return response()->json([
            'report' => $report,
            'reported_entity' => $entityData,
        ]);
    }

    /**
     * Review and take action on a report (Admin only)
     */
    public function review(Request $request, Report $report)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:under_review,resolved,dismissed',
            'action_taken' => 'nullable|in:none,warned,suspended,deleted,contacted',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $admin = $request->user();

        $report->update([
            'status' => $request->status,
            'action_taken' => $request->action_taken ?? 'none',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        // Log admin action
        AdminActionLog::logAction(
            $admin->id,
            'review_report',
            'Report',
            $report->id,
            [
                'report_reason' => $report->reason,
                'reported_entity' => $report->reported_entity_type . ' #' . $report->reported_entity_id,
                'action_taken' => $request->action_taken,
                'status' => $request->status,
            ],
            $request->admin_notes
        );

        return response()->json([
            'message' => 'Report reviewed successfully',
            'report' => $report->load(['reporter', 'reviewer']),
        ]);
    }

    /**
     * Delete a report (Admin only)
     */
    public function destroy(Report $report)
    {
        $admin = request()->user();

        // Log deletion
        AdminActionLog::logAction(
            $admin->id,
            'delete_report',
            'Report',
            $report->id,
            [
                'reason' => $report->reason,
                'reporter' => $report->reporter->name,
            ]
        );

        // Delete evidence file if exists
        if ($report->evidence_path) {
            Storage::disk('public')->delete($report->evidence_path);
        }

        $report->delete();

        return response()->json([
            'message' => 'Report deleted successfully',
        ]);
    }

    /**
     * Get report statistics (Admin only)
     */
    public function statistics()
    {
        return response()->json([
            'total' => Report::count(),
            'pending' => Report::where('status', 'pending')->count(),
            'under_review' => Report::where('status', 'under_review')->count(),
            'resolved' => Report::where('status', 'resolved')->count(),
            'dismissed' => Report::where('status', 'dismissed')->count(),
            'by_reason' => Report::selectRaw('reason, COUNT(*) as count')
                ->groupBy('reason')
                ->get(),
            'recent' => Report::with('reporter')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ]);
    }
}
