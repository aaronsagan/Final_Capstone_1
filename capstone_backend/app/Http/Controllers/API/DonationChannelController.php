<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DonationChannel;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DonationChannelController extends Controller
{
    /**
     * Get all donation channels for a specific campaign (PUBLIC - for donors viewing campaign)
     */
    public function getCampaignChannels(Campaign $campaign)
    {
        $channels = $campaign->donationChannels()
            ->where('is_active', true)
            ->get();

        return response()->json($channels);
    }

    /**
     * Get all donation channels for the authenticated charity
     */
    public function getCharityChannels(Request $request)
    {
        $user = $request->user();
        
        if (!$user->charity) {
            return response()->json(['error' => 'Charity not found'], 404);
        }

        $channels = DonationChannel::where('charity_id', $user->charity->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($channels);
    }

    /**
     * Store a new donation channel for the charity
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        if (!$user->charity) {
            return response()->json(['error' => 'Charity not found'], 404);
        }

        // Check if charity has reached the limit (10 channels)
        $existingCount = DonationChannel::where('charity_id', $user->charity->id)->count();
        if ($existingCount >= 10) {
            return response()->json(['error' => 'You have reached the maximum limit of 10 donation channels'], 422);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:gcash,maya,paymaya,paypal,bank,bank_transfer,ewallet,other',
            'label' => 'nullable|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'qr_code' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'details' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['charity_id'] = $user->charity->id;
        $data['is_active'] = true;

        // Handle QR code upload
        if ($request->hasFile('qr_code')) {
            $file = $request->file('qr_code');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('donation_channels', $filename, 'public');
            $data['qr_code_path'] = $path;
        }

        // Auto-generate label if not provided
        if (empty($data['label'])) {
            $data['label'] = ucfirst($data['type']) . ' - ' . $data['account_number'];
        }

        $channel = DonationChannel::create($data);

        return response()->json([
            'message' => 'Donation channel created successfully',
            'channel' => $channel
        ], 201);
    }

    /**
     * Update a donation channel
     */
    public function update(Request $request, DonationChannel $channel)
    {
        $user = $request->user();
        
        if (!$user->charity || $channel->charity_id !== $user->charity->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|required|string|in:gcash,maya,paymaya,paypal,bank,bank_transfer,ewallet,other',
            'label' => 'nullable|string|max:255',
            'account_name' => 'sometimes|required|string|max:255',
            'account_number' => 'sometimes|required|string|max:255',
            'qr_code' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'details' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Handle QR code upload
        if ($request->hasFile('qr_code')) {
            // Delete old QR code if exists
            if ($channel->qr_code_path) {
                Storage::disk('public')->delete($channel->qr_code_path);
            }

            $file = $request->file('qr_code');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('donation_channels', $filename, 'public');
            $data['qr_code_path'] = $path;
        }

        $channel->update($data);

        return response()->json([
            'message' => 'Donation channel updated successfully',
            'channel' => $channel
        ]);
    }

    /**
     * Delete a donation channel
     */
    public function destroy(Request $request, DonationChannel $channel)
    {
        $user = $request->user();
        
        if (!$user->charity || $channel->charity_id !== $user->charity->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Delete QR code file if exists
        if ($channel->qr_code_path) {
            Storage::disk('public')->delete($channel->qr_code_path);
        }

        $channel->delete();

        return response()->json(['message' => 'Donation channel deleted successfully']);
    }

    /**
     * Toggle active status
     */
    public function toggleActive(Request $request, DonationChannel $channel)
    {
        $user = $request->user();
        
        if (!$user->charity || $channel->charity_id !== $user->charity->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $channel->update(['is_active' => !$channel->is_active]);

        return response()->json([
            'message' => 'Channel status updated successfully',
            'channel' => $channel
        ]);
    }

    /**
     * Attach donation channels to a campaign
     */
    public function attachToCampaign(Request $request, Campaign $campaign)
    {
        $user = $request->user();
        
        if (!$user->charity || $campaign->charity_id !== $user->charity->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'channel_ids' => 'required|array',
            'channel_ids.*' => 'required|exists:donation_channels,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verify all channels belong to the charity
        $channels = DonationChannel::whereIn('id', $request->channel_ids)
            ->where('charity_id', $user->charity->id)
            ->pluck('id');

        if ($channels->count() !== count($request->channel_ids)) {
            return response()->json(['error' => 'Some channels do not belong to your charity'], 422);
        }

        // Sync the channels (replaces existing associations)
        $campaign->donationChannels()->sync($request->channel_ids);

        return response()->json([
            'message' => 'Donation channels linked to campaign successfully',
            'channels' => $campaign->donationChannels
        ]);
    }
}
