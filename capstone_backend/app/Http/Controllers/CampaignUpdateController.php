<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CampaignUpdateController extends Controller
{
    /**
     * Get all updates for a campaign
     */
    public function index($campaignId)
    {
        try {
            $updates = CampaignUpdate::where('campaign_id', $campaignId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json(['data' => $updates]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch campaign updates: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch updates'], 500);
        }
    }

    /**
     * Create a new campaign update
     */
    public function store(Request $request, $campaignId)
    {
        try {
            $campaign = Campaign::findOrFail($campaignId);
            $user = auth()->user();

            // Check ownership - only charity owner can create updates
            if (!$user || $user->role !== 'charity_admin' || !$user->charity || $campaign->charity_id !== $user->charity->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'is_milestone' => 'nullable|boolean',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('campaign_updates', 'public');
            }

            $update = CampaignUpdate::create([
                'campaign_id' => $campaignId,
                'title' => $validated['title'],
                'content' => $validated['content'],
                'is_milestone' => $validated['is_milestone'] ?? false,
                'image_path' => $imagePath,
            ]);

            return response()->json($update, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create campaign update: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create update'], 500);
        }
    }

    /**
     * Update an existing campaign update
     */
    public function update(Request $request, $id)
    {
        try {
            $update = CampaignUpdate::findOrFail($id);
            $campaign = $update->campaign;
            $user = auth()->user();

            // Check ownership
            if (!$user || $user->role !== 'charity_admin' || !$user->charity || $campaign->charity_id !== $user->charity->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'is_milestone' => 'nullable|boolean',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($update->image_path) {
                    Storage::disk('public')->delete($update->image_path);
                }
                $validated['image_path'] = $request->file('image')->store('campaign_updates', 'public');
            }

            $update->update([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'is_milestone' => $validated['is_milestone'] ?? $update->is_milestone,
                'image_path' => $validated['image_path'] ?? $update->image_path,
            ]);

            return response()->json($update);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update campaign update: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update'], 500);
        }
    }

    /**
     * Delete a campaign update
     */
    public function destroy($id)
    {
        try {
            $update = CampaignUpdate::findOrFail($id);
            $campaign = $update->campaign;
            $user = auth()->user();

            // Check ownership
            if (!$user || $user->role !== 'charity_admin' || !$user->charity || $campaign->charity_id !== $user->charity->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Delete image if exists
            if ($update->image_path) {
                Storage::disk('public')->delete($update->image_path);
            }

            $update->delete();

            return response()->json(['message' => 'Update deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to delete campaign update: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete update'], 500);
        }
    }

    /**
     * Get milestone updates for a campaign
     */
    public function getMilestones($campaignId)
    {
        try {
            $milestones = CampaignUpdate::where('campaign_id', $campaignId)
                ->where('is_milestone', true)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            return response()->json(['data' => $milestones]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch milestones: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch milestones'], 500);
        }
    }

    /**
     * Get update statistics for a campaign
     */
    public function getStats($campaignId)
    {
        try {
            $totalUpdates = CampaignUpdate::where('campaign_id', $campaignId)->count();
            $milestoneCount = CampaignUpdate::where('campaign_id', $campaignId)
                ->where('is_milestone', true)
                ->count();
            $lastUpdate = CampaignUpdate::where('campaign_id', $campaignId)
                ->latest()
                ->first();

            return response()->json([
                'total_updates' => $totalUpdates,
                'milestone_count' => $milestoneCount,
                'last_update_date' => $lastUpdate ? $lastUpdate->created_at : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch update stats: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch stats'], 500);
        }
    }
}
