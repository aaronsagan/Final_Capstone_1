<?php

use Illuminate\Support\Facades\Route;
use App\Models\Campaign;

Route::get('/test-campaign/{id}', function ($id) {
    $campaign = Campaign::with(['donationChannels', 'charity'])->find($id);
    
    if (!$campaign) {
        return response()->json(['error' => 'Campaign not found'], 404);
    }
    
    return response()->json([
        'id' => $campaign->id,
        'title' => $campaign->title,
        'description' => $campaign->description,
        'problem' => $campaign->problem,
        'solution' => $campaign->solution,
        'expected_outcome' => $campaign->expected_outcome,
        'donation_channels' => $campaign->donationChannels->map(fn($ch) => [
            'id' => $ch->id,
            'type' => $ch->type,
            'label' => $ch->label,
            'account_name' => $ch->account_name,
        ]),
        'channels_count' => $campaign->donationChannels->count(),
    ], 200, [], JSON_PRETTY_PRINT);
});
