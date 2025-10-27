<?php

namespace App\Http\Controllers;

use App\Models\CampaignComment;
use App\Models\Campaign;
use App\Models\AdminActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CampaignCommentController extends Controller
{
    /**
     * Get approved comments for a campaign (Public)
     */
    public function index(Campaign $campaign)
    {
        $comments = CampaignComment::where('campaign_id', $campaign->id)
            ->approved()
            ->with('user:id,name,profile_image')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($comments);
    }

    /**
     * Submit a comment (Donor only)
     */
    public function store(Request $request, Campaign $campaign)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|min:5|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        // Check if user is a donor
        if ($user->role !== 'donor') {
            return response()->json(['error' => 'Only donors can comment on campaigns'], 403);
        }

        $comment = CampaignComment::create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'comment' => $request->comment,
            'status' => 'pending', // Requires moderation
        ]);

        return response()->json([
            'message' => 'Comment submitted successfully. It will be visible after moderation.',
            'comment' => $comment->load('user:id,name,profile_image'),
        ], 201);
    }

    /**
     * Get pending comments for moderation (Admin only)
     */
    public function pending()
    {
        $comments = CampaignComment::pending()
            ->with(['user:id,name,email', 'campaign:id,title'])
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return response()->json($comments);
    }

    /**
     * Moderate a comment (Admin only)
     */
    public function moderate(Request $request, CampaignComment $comment)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'moderation_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $admin = $request->user();

        $comment->update([
            'status' => $request->status,
            'moderated_by' => $admin->id,
            'moderated_at' => now(),
            'moderation_notes' => $request->moderation_notes,
        ]);

        // Log admin action
        AdminActionLog::logAction(
            $admin->id,
            'moderate_comment',
            'CampaignComment',
            $comment->id,
            [
                'campaign_title' => $comment->campaign->title,
                'commenter' => $comment->user->name,
                'action' => $request->status,
            ],
            $request->moderation_notes
        );

        return response()->json([
            'message' => "Comment {$request->status} successfully",
            'comment' => $comment->load(['user', 'moderator']),
        ]);
    }

    /**
     * Delete a comment (Admin only)
     */
    public function destroy(CampaignComment $comment)
    {
        $admin = request()->user();

        // Log deletion
        AdminActionLog::logAction(
            $admin->id,
            'delete_comment',
            'CampaignComment',
            $comment->id,
            [
                'campaign_title' => $comment->campaign->title,
                'commenter' => $comment->user->name,
                'comment_preview' => substr($comment->comment, 0, 100),
            ]
        );

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully',
        ]);
    }

    /**
     * Get comment statistics (Admin only)
     */
    public function statistics()
    {
        return response()->json([
            'total' => CampaignComment::count(),
            'pending' => CampaignComment::where('status', 'pending')->count(),
            'approved' => CampaignComment::where('status', 'approved')->count(),
            'rejected' => CampaignComment::where('status', 'rejected')->count(),
            'recent_pending' => CampaignComment::pending()
                ->with(['user:id,name', 'campaign:id,title'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ]);
    }
}
