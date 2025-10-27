<?php

use Illuminate\Support\Facades\Route;
use App\Models\CampaignUpdate;
use App\Models\Campaign;

Route::get('/test-updates/{campaignId}', function ($campaignId) {
    $updates = CampaignUpdate::where('campaign_id', $campaignId)
        ->orderBy('created_at', 'desc')
        ->get();
    
    $campaign = Campaign::find($campaignId);
    
    return response()->json([
        'campaign_id' => $campaignId,
        'campaign_title' => $campaign ? $campaign->title : 'Not Found',
        'updates_count' => $updates->count(),
        'updates' => $updates->map(fn($u) => [
            'id' => $u->id,
            'title' => $u->title,
            'content' => substr($u->content, 0, 50) . '...',
            'is_milestone' => $u->is_milestone,
            'created_at' => $u->created_at->toDateTimeString(),
        ]),
    ], 200, [], JSON_PRETTY_PRINT);
});
