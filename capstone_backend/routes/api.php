<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
  AuthController, CharityController, CampaignController, CampaignUpdateController, DonationController, FundUsageController, CharityPostController, TransparencyController, CharityFollowController, NotificationController, ReportController, CampaignCommentController, CategoryController, VolunteerController, LeaderboardController, DocumentExpiryController, LocationController, DonorRegistrationController, AnalyticsController
};
use App\Models\Charity;
use App\Models\Campaign;
use App\Models\CampaignUpdate;
use App\Http\Controllers\Admin\{VerificationController, AdminActionLogController};

// Health
Route::get('/ping', fn () => ['ok' => true, 'time' => now()->toDateTimeString()]);

// TEST ROUTE - Check campaign data
Route::get('/test-campaign/{id}', function ($id) {
    $campaign = \App\Models\Campaign::with(['donationChannels', 'charity'])->find($id);
    if (!$campaign) return response()->json(['error' => 'Campaign not found'], 404);
    return response()->json([
        'id' => $campaign->id,
        'title' => $campaign->title,
        'problem' => $campaign->problem,
        'solution' => $campaign->solution,
        'expected_outcome' => $campaign->expected_outcome,
        'channels' => $campaign->donationChannels->map(fn($ch) => ['id' => $ch->id, 'type' => $ch->type, 'label' => $ch->label]),
        'channels_count' => $campaign->donationChannels->count(),
    ], 200, [], JSON_PRETTY_PRINT);
});

// TEST ROUTE - Check campaign updates
Route::get('/test-updates/{campaignId}', function ($campaignId) {
    $campaign = Campaign::find($campaignId);
    $updates = CampaignUpdate::where('campaign_id', $campaignId)->orderBy('created_at', 'desc')->get();
    return response()->json([
        'campaign_id' => $campaignId,
        'campaign_title' => $campaign ? $campaign->title : 'Not Found',
        'updates_count' => $updates->count(),
        'updates' => $updates->map(fn($u) => [
            'id' => $u->id,
            'title' => $u->title,
            'content' => substr($u->content, 0, 80) . '...',
            'is_milestone' => $u->is_milestone,
            'image_path' => $u->image_path,
            'created_at' => $u->created_at->toDateTimeString(),
        ]),
    ], 200, [], JSON_PRETTY_PRINT);
});

// Philippine Locations API (Public)
Route::get('/locations', [LocationController::class,'index']);
Route::get('/locations/regions', [LocationController::class,'getRegions']);
Route::get('/locations/regions/{regionCode}/provinces', [LocationController::class,'getProvinces']);
Route::get('/locations/regions/{regionCode}/provinces/{provinceCode}/cities', [LocationController::class,'getCities']);

// Auth
Route::post('/auth/register', [AuthController::class,'registerDonor']);
Route::post('/auth/register-charity', [AuthController::class,'registerCharityAdmin']);
Route::post('/auth/login', [AuthController::class,'login']);
Route::post('/auth/logout', [AuthController::class,'logout'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class,'me'])->middleware('auth:sanctum');
Route::put('/me', [AuthController::class,'updateProfile'])->middleware('auth:sanctum');
Route::post('/me/change-password', [AuthController::class,'changePassword'])->middleware('auth:sanctum');
Route::post('/me/deactivate', [AuthController::class,'deactivateAccount'])->middleware('auth:sanctum');
Route::delete('/me', [AuthController::class,'deleteAccount'])->middleware('auth:sanctum');

// Donor Registration (multi-step)
Route::post('/donors/register/draft', [DonorRegistrationController::class,'saveDraft']);
Route::post('/donors/register/verification', [DonorRegistrationController::class,'uploadVerification']);
Route::post('/donors/register/submit', [DonorRegistrationController::class,'submit']);

// Public directory
Route::get('/charities', [CharityController::class,'index']);
Route::get('/charities/{charity}', [CharityController::class,'show']);
Route::get('/charities/{charity}/channels', [CharityController::class,'channels']);
Route::get('/charities/{charity}/campaigns', [CampaignController::class,'index']);
Route::get('/campaigns/{campaign}', [CampaignController::class,'show']);
Route::get('/campaigns/{campaign}/fund-usage', [FundUsageController::class,'publicIndex']);
Route::get('/campaigns/{campaign}/updates', [CampaignUpdateController::class,'index']);
Route::get('/campaigns/{campaign}/updates/milestones', [CampaignUpdateController::class,'getMilestones']);
Route::get('/campaigns/{campaign}/updates/stats', [CampaignUpdateController::class,'getStats']);
Route::get('/campaigns/{campaign}/supporters', [CampaignController::class,'getSupporters']);
Route::get('/campaigns/{campaign}/donations', [CampaignController::class,'getDonations']);
Route::get('/campaigns/{campaign}/stats', [CampaignController::class,'getStats']);

// Public charity posts (for donor news feed and charity profile)
Route::get('/posts', [CharityPostController::class,'index']);
Route::get('/charities/{charity}/posts', [CharityPostController::class,'getCharityPosts']);

// Public updates (for donor viewing)
Route::get('/charities/{charity}/updates', [\App\Http\Controllers\UpdateController::class,'getCharityUpdates']);

// Public categories and campaign comments
Route::get('/categories', [CategoryController::class,'index']);
Route::get('/campaigns/{campaign}/comments', [CampaignCommentController::class,'index']);

// Public donation channels
Route::get('/campaigns/{campaign}/donation-channels', [\App\Http\Controllers\DonationChannelController::class,'index']);
Route::get('/charities/{charity}/donation-channels', [\App\Http\Controllers\DonationChannelController::class,'getCharityChannelsPublic']);

// Public leaderboards
Route::get('/leaderboard/donors', [LeaderboardController::class,'topDonors']);
Route::get('/leaderboard/charities', [LeaderboardController::class,'topCharities']);
Route::get('/leaderboard/stats', [LeaderboardController::class,'donationStats']);
Route::get('/leaderboard/period', [LeaderboardController::class,'leaderboardByPeriod']);
Route::get('/charities/{charity}/leaderboard', [LeaderboardController::class,'topDonorsForCharity']);

// Public charity documents (for viewing by donors and public)
Route::get('/charities/{charity}/documents', [CharityController::class,'getDocuments']);

// Document viewing and downloading (authenticated users)
Route::middleware(['auth:sanctum'])->group(function(){
  Route::get('/documents/{document}/view', [\App\Http\Controllers\DocumentController::class,'view']);
  Route::get('/documents/{document}/download', [\App\Http\Controllers\DocumentController::class,'download']);
});

// Charity follow system (for donors)
Route::middleware(['auth:sanctum','role:donor'])->group(function(){
  Route::post('/charities/{charity}/follow', [CharityFollowController::class,'toggleFollow']);
  Route::get('/charities/{charity}/follow-status', [CharityFollowController::class,'getFollowStatus']);
  Route::get('/me/followed-charities', [CharityFollowController::class,'myFollowedCharities']);
});

// Public charity follow stats
Route::get('/charities/{charity}/followers-count', [CharityFollowController::class,'getFollowersCount']);
Route::get('/charities/{charity}/followers', [CharityFollowController::class,'getFollowers']);

// Public transparency (for approved charities only)
Route::get('/charities/{charity}/transparency', [TransparencyController::class,'publicTransparency']);

// Donor transparency dashboard
Route::middleware(['auth:sanctum','role:donor'])->group(function(){
  Route::get('/me/transparency', [TransparencyController::class,'donorTransparency']);
});

// Charity admin transparency dashboard
Route::middleware(['auth:sanctum','role:charity_admin'])->group(function(){
  Route::get('/charities/{charity}/transparency', [TransparencyController::class,'charityTransparency']);
});

// Donor actions
Route::middleware(['auth:sanctum','role:donor'])->group(function(){
  Route::post('/donations', [DonationController::class,'store']);
  Route::post('/donations/{donation}/proof', [DonationController::class,'uploadProof']);
  Route::post('/campaigns/{campaign}/donate', [DonationController::class,'submitManualDonation']);
  Route::post('/charities/{charity}/donate', [DonationController::class,'submitCharityDonation']);
  Route::get('/me/donations', [DonationController::class,'myDonations']);
  Route::get('/donations/{donation}/receipt', [DonationController::class,'downloadReceipt']);
  
  // Reports (Donor can submit reports)
  Route::post('/reports', [ReportController::class,'store']);
  Route::get('/me/reports', [ReportController::class,'myReports']);
  
  // Campaign Comments (Donor can comment)
  Route::post('/campaigns/{campaign}/comments', [CampaignCommentController::class,'store']);
});

// Notifications (available to any authenticated user role)
Route::middleware(['auth:sanctum'])->group(function(){
  Route::get('/me/notifications', [NotificationController::class,'index']);
  Route::post('/notifications/{notification}/read', [NotificationController::class,'markAsRead']);
  Route::post('/notifications/mark-all-read', [NotificationController::class,'markAllAsRead']);
  Route::get('/notifications/unread-count', [NotificationController::class,'unreadCount']);
  Route::delete('/notifications/{notification}', [NotificationController::class,'destroy']);
  
  // Update interactions (available to any authenticated user)
  Route::post('/updates/{id}/like', [\App\Http\Controllers\UpdateController::class,'toggleLike']);
  Route::post('/updates/{id}/share', [\App\Http\Controllers\UpdateController::class,'shareUpdate']);
  Route::get('/updates/{id}/comments', [\App\Http\Controllers\UpdateController::class,'getComments']);
  Route::post('/updates/{id}/comments', [\App\Http\Controllers\UpdateController::class,'addComment']);
  Route::put('/comments/{id}', [\App\Http\Controllers\UpdateController::class,'updateComment']);
  Route::delete('/comments/{id}', [\App\Http\Controllers\UpdateController::class,'deleteComment']);
  Route::post('/comments/{id}/like', [\App\Http\Controllers\UpdateController::class,'toggleCommentLike']);
});

// System admin (for recurring donations processing and security)
Route::middleware(['auth:sanctum','role:admin'])->group(function(){
  Route::post('/admin/process-recurring-donations', [DonationController::class,'processRecurringDonations']);
  Route::get('/admin/security/activity-logs', [\App\Http\Controllers\Admin\SecurityController::class,'activityLogs']);
  Route::get('/admin/compliance/report', [\App\Http\Controllers\Admin\ComplianceController::class,'generateReport']);
});

// Charity admin
Route::middleware(['auth:sanctum','role:charity_admin'])->group(function(){
  Route::post('/charities', [CharityController::class,'store']);
  Route::put('/charities/{charity}', [CharityController::class,'update']);
  Route::post('/charity/profile/update', [CharityController::class,'updateProfile']);
  Route::post('/charities/{charity}/documents', [CharityController::class,'uploadDocument']);

  Route::post('/charities/{charity}/channels', [CharityController::class,'storeChannel']);

  Route::post('/charities/{charity}/campaigns', [CampaignController::class,'store']);
  Route::put('/campaigns/{campaign}', [CampaignController::class,'update']);
  Route::delete('/campaigns/{campaign}', [CampaignController::class,'destroy']);

  // Campaign Updates Management (Charity Admin Only)
  Route::post('/campaigns/{campaign}/updates', [CampaignUpdateController::class,'store']);
  Route::put('/campaign-updates/{id}', [CampaignUpdateController::class,'update']);
  Route::delete('/campaign-updates/{id}', [CampaignUpdateController::class,'destroy']);

  // Donation Channels Management
  Route::get('/charity/donation-channels', [\App\Http\Controllers\DonationChannelController::class,'getCharityChannels']);
  Route::post('/charity/donation-channels', [\App\Http\Controllers\DonationChannelController::class,'store']);
  Route::put('/donation-channels/{channel}', [\App\Http\Controllers\DonationChannelController::class,'update']);
  Route::delete('/donation-channels/{channel}', [\App\Http\Controllers\DonationChannelController::class,'destroy']);
  Route::post('/donation-channels/{channel}/toggle', [\App\Http\Controllers\DonationChannelController::class,'toggleActive']);
  Route::post('/campaigns/{campaign}/donation-channels/attach', [\App\Http\Controllers\DonationChannelController::class,'attachToCampaign']);

  Route::get('/charities/{charity}/donations', [DonationController::class,'charityInbox']);
  Route::patch('/donations/{donation}/confirm', [DonationController::class,'confirm']);
  Route::patch('/donations/{donation}/status', [DonationController::class,'updateStatus']);

  // Fund Usage Management (CRUD)
  Route::get('/campaigns/{campaignId}/fund-usage', [FundUsageController::class,'index']);
  Route::post('/campaigns/{campaign}/fund-usage', [FundUsageController::class,'store']);
  Route::put('/fund-usage/{id}', [FundUsageController::class,'update']);
  Route::delete('/fund-usage/{id}', [FundUsageController::class,'destroy']);
  
  // Charity posts management
  Route::get('/my-posts', [CharityPostController::class,'getMyPosts']);
  Route::post('/posts', [CharityPostController::class,'store']);
  Route::put('/posts/{post}', [CharityPostController::class,'update']);
  Route::delete('/posts/{post}', [CharityPostController::class,'destroy']);
  
  // Reports (Charity can submit reports about donors)
  Route::post('/reports', [ReportController::class,'store']);
  Route::get('/me/reports', [ReportController::class,'myReports']);
  
  // Volunteer Management
  Route::get('/charities/{charity}/volunteers', [VolunteerController::class,'index']);
  Route::post('/charities/{charity}/volunteers', [VolunteerController::class,'store']);
  Route::get('/charities/{charity}/volunteers/statistics', [VolunteerController::class,'statistics']);
  Route::get('/charities/{charity}/volunteers/{volunteer}', [VolunteerController::class,'show']);
  Route::put('/charities/{charity}/volunteers/{volunteer}', [VolunteerController::class,'update']);
  Route::delete('/charities/{charity}/volunteers/{volunteer}', [VolunteerController::class,'destroy']);
  
  // Document Expiry Status
  Route::get('/charities/{charity}/documents/expiry-status', [DocumentExpiryController::class,'getCharityDocumentStatus']);
  
  // Updates Management (Charity Admin)
  Route::get('/updates', [\App\Http\Controllers\UpdateController::class,'index']);
  Route::post('/updates', [\App\Http\Controllers\UpdateController::class,'store']);
  Route::put('/updates/{id}', [\App\Http\Controllers\UpdateController::class,'update']);
  Route::delete('/updates/{id}', [\App\Http\Controllers\UpdateController::class,'destroy']);
  Route::post('/updates/{id}/pin', [\App\Http\Controllers\UpdateController::class,'togglePin']);
  Route::patch('/comments/{id}/hide', [\App\Http\Controllers\UpdateController::class,'hideComment']);
  
  // Bin/Trash Management
  Route::get('/updates/trash', [\App\Http\Controllers\UpdateController::class,'getTrashed']);
  Route::post('/updates/{id}/restore', [\App\Http\Controllers\UpdateController::class,'restore']);
  Route::delete('/updates/{id}/force', [\App\Http\Controllers\UpdateController::class,'forceDelete']);
});

// System admin
Route::middleware(['auth:sanctum','role:admin'])->group(function(){
  Route::get('/admin/verifications', [VerificationController::class,'index']);
  Route::get('/admin/charities', [VerificationController::class,'getAllCharities']);
  Route::get('/admin/users', [VerificationController::class,'getUsers']);
  Route::patch('/admin/charities/{charity}/approve', [VerificationController::class,'approve']);
  Route::patch('/admin/charities/{charity}/reject', [VerificationController::class,'reject']);
  Route::patch('/admin/users/{user}/suspend', [VerificationController::class,'suspendUser']);
  Route::patch('/admin/users/{user}/activate', [VerificationController::class,'activateUser']);
  
  // Reports Management
  Route::get('/admin/reports', [ReportController::class,'index']);
  Route::get('/admin/reports/statistics', [ReportController::class,'statistics']);
  Route::get('/admin/reports/{report}', [ReportController::class,'show']);
  Route::patch('/admin/reports/{report}/review', [ReportController::class,'review']);
  Route::delete('/admin/reports/{report}', [ReportController::class,'destroy']);
  
  // Admin Action Logs
  Route::get('/admin/action-logs', [AdminActionLogController::class,'index']);
  Route::get('/admin/action-logs/statistics', [AdminActionLogController::class,'statistics']);
  Route::get('/admin/action-logs/export', [AdminActionLogController::class,'export']);
  
  // Category Management
  Route::get('/admin/categories', [CategoryController::class,'adminIndex']);
  Route::post('/admin/categories', [CategoryController::class,'store']);
  Route::get('/admin/categories/statistics', [CategoryController::class,'statistics']);
  Route::put('/admin/categories/{category}', [CategoryController::class,'update']);
  Route::delete('/admin/categories/{category}', [CategoryController::class,'destroy']);
  
  // Comment Moderation
  Route::get('/admin/comments/pending', [CampaignCommentController::class,'pending']);
  Route::get('/admin/comments/statistics', [CampaignCommentController::class,'statistics']);
  Route::patch('/admin/comments/{comment}/moderate', [CampaignCommentController::class,'moderate']);
  Route::delete('/admin/comments/{comment}', [CampaignCommentController::class,'destroy']);
  
  // Document Expiry Management
  Route::get('/admin/documents/expiring', [DocumentExpiryController::class,'getExpiringDocuments']);
  Route::get('/admin/documents/expired', [DocumentExpiryController::class,'getExpiredDocuments']);
  Route::get('/admin/documents/expiry-statistics', [DocumentExpiryController::class,'getExpiryStatistics']);
  Route::patch('/admin/documents/{document}/expiry', [DocumentExpiryController::class,'updateDocumentExpiry']);
});

// Analytics Endpoints
Route::middleware(['auth:sanctum'])->group(function(){
  // Public analytics (available to all authenticated users)
  Route::get('/analytics/campaigns/types', [AnalyticsController::class,'campaignsByType']);
  Route::get('/analytics/campaigns/trending', [AnalyticsController::class,'trendingCampaigns']);
  Route::get('/analytics/campaigns/{type}/stats', [AnalyticsController::class,'campaignTypeStats']);
  Route::get('/analytics/campaigns/{campaignId}/summary', [AnalyticsController::class,'campaignSummary']);
  
  // Advanced analytics (Phase 6)
  Route::get('/analytics/campaigns/{type}/advanced', [AnalyticsController::class,'advancedTypeAnalytics']);
  Route::get('/analytics/trending-explanation/{type}', [AnalyticsController::class,'trendingExplanation']);
  
  // Enhanced trending & activity analytics
  Route::get('/analytics/growth-by-type', [AnalyticsController::class,'growthByType']);
  Route::get('/analytics/most-improved', [AnalyticsController::class,'mostImprovedCampaign']);
  Route::get('/analytics/activity-timeline', [AnalyticsController::class,'activityTimeline']);
  
  // Overview analytics
  Route::get('/analytics/overview', [AnalyticsController::class,'getOverviewSummary']);
  Route::get('/analytics/trends', [AnalyticsController::class,'getTrendsData']);
  Route::get('/analytics/insights', [AnalyticsController::class,'getInsights']);
  Route::get('/analytics/summary', [AnalyticsController::class,'summary']);
  Route::get('/analytics/campaigns/locations', [AnalyticsController::class,'campaignLocations']);
  Route::get('/analytics/campaigns/by-location', [AnalyticsController::class,'getCampaignsByLocation']);
  Route::get('/analytics/campaigns/location-summary', [AnalyticsController::class,'getLocationSummary']);
  Route::get('/analytics/campaigns/location-filters', [AnalyticsController::class,'getLocationFilters']);
  Route::get('/analytics/campaigns/beneficiaries', [AnalyticsController::class,'getCampaignBeneficiaryDistribution']);
  Route::get('/analytics/campaigns/temporal', [AnalyticsController::class,'temporalTrends']);
  Route::get('/analytics/campaigns/fund-ranges', [AnalyticsController::class,'fundRanges']);
  Route::get('/analytics/campaigns/top-performers', [AnalyticsController::class,'topPerformers']);
  
  // Donor-specific analytics (protected)
  Route::get('/analytics/donors/{donorId}/summary', [AnalyticsController::class,'donorSummary']);
  
  // Campaign filtering (Phase 7)
  Route::get('/campaigns/filter', [AnalyticsController::class,'filterCampaigns']);
  Route::get('/campaigns/filter-options', [AnalyticsController::class,'filterOptions']);
});

// routes/api.php
Route::get('/metrics', function () {
    return [
        'total_users' => \App\Models\User::count(),
        'total_donors' => \App\Models\User::where('role', 'donor')->count(),
        'total_charity_admins' => \App\Models\User::where('role', 'charity_admin')->count(),
        'charities' => \App\Models\Charity::where('verification_status','approved')->count(),
        'pending_verifications' => \App\Models\Charity::where('verification_status','pending')->count(),
        'campaigns' => \App\Models\Campaign::count(),
        'donations' => \App\Models\Donation::count(),
    ];
});
