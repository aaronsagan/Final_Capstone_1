<?php

namespace App\Http\Controllers;

use App\Models\DonationChannel;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class DonationChannelController extends Controller
{
    /**
     * Get all donation channels for a specific campaign (PUBLIC - for donors viewing campaign)
     */
    public function index(Campaign $campaign)
    {
        \Log::info('Fetching donation channels for campaign', [
            'campaign_id' => $campaign->id,
            'campaign_title' => $campaign->title
        ]);

        $channels = $campaign->donationChannels()
            ->where('is_active', true)
            ->get();

        \Log::info('Found donation channels', [
            'campaign_id' => $campaign->id,
            'count' => $channels->count(),
            'channel_ids' => $channels->pluck('id')->toArray()
        ]);

        return response()->json($channels);
    }

    /**
     * Get all donation channels for a specific charity (PUBLIC - for donors donating directly)
     */
    public function getCharityChannelsPublic($charityId)
    {
        $channels = DonationChannel::where('charity_id', $charityId)
            ->where('is_active', true)
            ->get();

        return response()->json($channels);
    }

    public function getCharityChannels(Request $request)
    {
        $user = $request->user();
        $charityId = $user->charity_id ?? null;
        
        if (!$charityId) {
            $charity = \App\Models\Charity::where('owner_id', $user->id)->first();
            if (!$charity) {
                return response()->json(['error' => 'No charity found'], 404);
            }
            $charityId = $charity->id;
        }

        $channels = DonationChannel::where('charity_id', $charityId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($channels);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $charityId = $user->charity_id ?? null;
        
        // If charity_id is not directly on user, try the relationship
        if (!$charityId) {
            $charity = \App\Models\Charity::where('owner_id', $user->id)->first();
            if (!$charity) {
                return response()->json(['error' => 'No charity found for this user'], 404);
            }
            $charityId = $charity->id;
        }

        // Check limit
        $count = DonationChannel::where('charity_id', $charityId)->count();
        if ($count >= 10) {
            return response()->json(['error' => 'Maximum 10 channels allowed'], 422);
        }

        $request->validate([
            'type' => 'required|string',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'label' => 'nullable|string|max:255',
            'qr_code' => 'nullable|image|max:2048',
        ]);

        $data = [
            'charity_id' => $charityId,
            'type' => $request->type,
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
            'label' => $request->label ?? ucfirst($request->type) . ' - ' . $request->account_number,
            'is_active' => true,
        ];

        if ($request->hasFile('qr_code')) {
            $path = $request->file('qr_code')->store('donation_channels', 'public');
            $data['qr_code_path'] = $path;
        }

        $channel = DonationChannel::create($data);

        return response()->json([
            'message' => 'Donation channel created successfully',
            'channel' => $channel
        ], 201);
    }

    // Update a donation channel
    public function update(Request $request, DonationChannel $channel)
    {
        $user = $request->user();
        $charityId = $user->charity_id ?? $user->charity->id ?? null;

        // Verify ownership
        if ($channel->charity_id !== $charityId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'type' => 'sometimes|in:gcash,maya,paymaya,paypal,bank,bank_transfer,ewallet,other',
            'label' => 'sometimes|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'qr_code' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'details' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        // Handle QR code upload
        if ($request->hasFile('qr_code')) {
            // Delete old QR code if exists
            if ($channel->qr_code_path) {
                Storage::disk('public')->delete($channel->qr_code_path);
            }
            $validated['qr_code_path'] = $request->file('qr_code')->store('donation_channels/qr_codes', 'public');
        }

        $channel->update($validated);

        return response()->json([
            'message' => 'Donation channel updated successfully',
            'channel' => $channel
        ]);
    }

    // Delete a donation channel
    public function destroy(Request $request, DonationChannel $channel)
    {
        $user = $request->user();
        $charityId = $user->charity_id ?? $user->charity->id ?? null;

        // Verify ownership
        if ($channel->charity_id !== $charityId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete QR code if exists
        if ($channel->qr_code_path) {
            Storage::disk('public')->delete($channel->qr_code_path);
        }

        $channel->delete();

        return response()->json([
            'message' => 'Donation channel deleted successfully'
        ]);
    }

    // Toggle active status
    public function toggleActive(Request $request, DonationChannel $channel)
    {
        $user = $request->user();
        $charityId = $user->charity_id ?? $user->charity->id ?? null;

        // Verify ownership
        if ($channel->charity_id !== $charityId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $channel->update(['is_active' => !$channel->is_active]);

        return response()->json([
            'message' => 'Donation channel status updated',
            'channel' => $channel
        ]);
    }

    /**
     * Attach donation channels to a campaign
     */
    public function attachToCampaign(Request $request, Campaign $campaign)
    {
        \Log::info('Attaching donation channels to campaign', [
            'campaign_id' => $campaign->id,
            'request_data' => $request->all(),
            'user_id' => $request->user()->id
        ]);

        $user = $request->user();
        $charity = $user->charity()->first();
        
        if (!$charity) {
            \Log::error('No charity found for user', ['user_id' => $user->id]);
            return response()->json(['error' => 'No charity found for this user'], 404);
        }
        
        if ($campaign->charity_id !== $charity->id) {
            \Log::error('Campaign does not belong to charity', [
                'campaign_id' => $campaign->id,
                'campaign_charity_id' => $campaign->charity_id,
                'user_charity_id' => $charity->id
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'channel_ids' => 'required|array',
            'channel_ids.*' => 'required|exists:donation_channels,id',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed for channel attachment', [
                'errors' => $validator->errors()
            ]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verify all channels belong to the charity
        $channels = DonationChannel::whereIn('id', $request->channel_ids)
            ->where('charity_id', $charity->id)
            ->pluck('id')
            ->toArray();

        \Log::info('Verified channels', [
            'requested' => $request->channel_ids,
            'found' => $channels,
            'charity_id' => $charity->id
        ]);

        if (count($channels) !== count($request->channel_ids)) {
            \Log::error('Some channels do not belong to charity', [
                'requested' => $request->channel_ids,
                'found' => $channels
            ]);
            return response()->json(['error' => 'Some channels do not belong to your charity'], 422);
        }

        // Sync the channels (replaces existing associations)
        $campaign->donationChannels()->sync($request->channel_ids);
        
        \Log::info('Channels synced successfully', [
            'campaign_id' => $campaign->id,
            'channel_ids' => $request->channel_ids
        ]);

        return response()->json([
            'message' => 'Donation channels linked to campaign successfully',
            'channels' => $campaign->donationChannels()->get()
        ]);
    }
}
