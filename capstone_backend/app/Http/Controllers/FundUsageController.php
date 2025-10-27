<?php

namespace App\Http\Controllers;

use App\Models\{FundUsageLog, Campaign, Charity};
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class FundUsageController extends Controller
{
    /**
     * Get fund usage logs for a campaign with summary data
     */
    public function index($campaignId)
    {
        try {
            $fundUsages = FundUsageLog::where('campaign_id', $campaignId)
                ->orderBy('spent_at', 'desc')
                ->get();

            $totalSpent = $fundUsages->sum('amount');
            
            // Group by category for breakdown
            $breakdown = $fundUsages->groupBy('category')->map(function ($items, $category) {
                return [
                    'category' => ucfirst($category),
                    'amount' => $items->sum('amount'),
                    'count' => $items->count(),
                ];
            })->values();

            return response()->json([
                'data' => $fundUsages,
                'summary' => [
                    'total_spent' => $totalSpent,
                    'total_entries' => $fundUsages->count(),
                    'last_entry_date' => $fundUsages->first()?->spent_at,
                    'breakdown' => $breakdown,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch fund usage logs: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch fund usage logs'], 500);
        }
    }

    // public view of a campaign's spending
    public function publicIndex(Campaign $campaign){
        return $campaign->charity->fundUsageLogs()
            ->where('campaign_id',$campaign->id)
            ->latest('spent_at')->paginate(20);
    }

    // charity admin adds an expense
    public function store(Request $r, Campaign $campaign){
        try {
            $charity = $campaign->charity;
            abort_unless($charity->owner_id === $r->user()->id, 403, 'You can only add fund usage logs to your own charity campaigns');

            $data = $r->validate([
                'amount' => 'required|numeric|min:0',
                'category' => 'required|in:supplies,staffing,transport,operations,other',
                'description' => 'nullable|string|max:1000',
                'spent_at' => 'nullable|date|before_or_equal:today',
                'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120'
            ]);

            $attachmentPath = null;
            if ($r->hasFile('attachment')) {
                $attachmentPath = $r->file('attachment')->store('fund_usage_attachments', 'public');
            }

            $fundUsageLog = FundUsageLog::create([
                'charity_id' => $charity->id,
                'campaign_id' => $campaign->id,
                'amount' => $data['amount'],
                'category' => $data['category'],
                'description' => $data['description'] ?? null,
                'spent_at' => $data['spent_at'] ?? now(),
                'attachment_path' => $attachmentPath
            ]);

            return response()->json([
                'message' => 'Fund usage log created successfully',
                'fund_usage_log' => $fundUsageLog->load(['charity', 'campaign'])
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Fund usage log creation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'campaign_id' => $campaign->id,
                'charity_id' => $charity->id ?? null,
                'user_id' => $r->user()->id
            ]);

            return response()->json([
                'message' => 'Failed to create fund usage log',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing fund usage log
     */
    public function update(Request $request, $id)
    {
        try {
            $fundUsage = FundUsageLog::findOrFail($id);
            $campaign = $fundUsage->campaign;
            $user = auth()->user();

            // Check ownership - only charity owner can update
            if (!$user || $user->role !== 'charity_admin' || !$user->charity || $campaign->charity_id !== $user->charity->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'amount' => 'required|numeric|min:0',
                'category' => 'required|in:supplies,staffing,transport,operations,other',
                'description' => 'nullable|string|max:1000',
                'spent_at' => 'nullable|date|before_or_equal:today',
                'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120'
            ]);

            // Handle file upload
            if ($request->hasFile('attachment')) {
                // Delete old attachment if exists
                if ($fundUsage->attachment_path) {
                    \Storage::disk('public')->delete($fundUsage->attachment_path);
                }
                $validated['attachment_path'] = $request->file('attachment')->store('fund_usage_attachments', 'public');
            }

            $fundUsage->update([
                'amount' => $validated['amount'],
                'category' => $validated['category'],
                'description' => $validated['description'] ?? $fundUsage->description,
                'spent_at' => $validated['spent_at'] ?? $fundUsage->spent_at,
                'attachment_path' => $validated['attachment_path'] ?? $fundUsage->attachment_path,
            ]);

            return response()->json([
                'message' => 'Fund usage log updated successfully',
                'fund_usage_log' => $fundUsage
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update fund usage log: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update fund usage log'], 500);
        }
    }

    /**
     * Delete a fund usage log
     */
    public function destroy($id)
    {
        try {
            $fundUsage = FundUsageLog::findOrFail($id);
            $campaign = $fundUsage->campaign;
            $user = auth()->user();

            // Check ownership - only charity owner can delete
            if (!$user || $user->role !== 'charity_admin' || !$user->charity || $campaign->charity_id !== $user->charity->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Delete attachment if exists
            if ($fundUsage->attachment_path) {
                \Storage::disk('public')->delete($fundUsage->attachment_path);
            }

            $fundUsage->delete();

            return response()->json(['message' => 'Fund usage log deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to delete fund usage log: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete fund usage log'], 500);
        }
    }
}
