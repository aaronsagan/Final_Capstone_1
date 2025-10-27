<?php

namespace App\Http\Controllers;

use App\Models\{Campaign, Donation, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    /**
     * Helper: Get charity ID from authenticated user or query param
     * ALWAYS filters by charity - ensures data privacy
     */
    private function getCharityId(Request $request)
    {
        // First try query param
        $charityId = $request->query('charity_id');
        
        // If not provided, get from authenticated user
        if (!$charityId && $request->user()) {
            $charity = \App\Models\Charity::where('owner_id', $request->user()->id)->first();
            if ($charity) {
                $charityId = $charity->id;
            }
        }
        
        return $charityId;
    }
    
    /**
     * GET /api/analytics/campaigns/types
     * Count campaigns by type (filtered by charity)
     */
    public function campaignsByType(Request $request)
    {
        $charityId = $this->getCharityId($request);
        
        // IMPORTANT: Always filter by charity for data privacy
        if (!$charityId) {
            return response()->json(['data' => [], 'total' => 0]);
        }
        
        $cacheKey = "analytics_campaigns_types_{$charityId}";
        
        $data = Cache::remember($cacheKey, 300, function () use ($charityId) {
            return Campaign::select('campaign_type', DB::raw('COUNT(*) as count'))
                ->whereNotNull('campaign_type')
                ->where('status', '!=', 'archived')
                ->where('charity_id', $charityId)  // ALWAYS filter by charity
                ->groupBy('campaign_type')
                ->orderBy('count', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'type' => $item->campaign_type,
                        'label' => ucwords(str_replace('_', ' ', $item->campaign_type)),
                        'count' => (int) $item->count,
                    ];
                });
        });
        
        return response()->json([
            'data' => $data,
            'total' => $data->sum('count'),
        ]);
    }
    
    /**
     * GET /api/analytics/campaigns/trending
     * Trending campaigns by recent donations
     */
    public function trendingCampaigns(Request $request)
    {
        try {
            $days = $request->query('days', 30);
            $limit = $request->query('limit', 10);
            
            // Use subquery to avoid GROUP BY issues
            $trending = Campaign::select(
                    'campaigns.id',
                    'campaigns.title',
                    'campaigns.charity_id',
                    'campaigns.campaign_type',
                    'campaigns.target_amount',
                    'campaigns.current_amount',
                    DB::raw('COUNT(donations.id) as donation_count'),
                    DB::raw('SUM(CASE WHEN donations.status = "completed" THEN donations.amount ELSE 0 END) as recent_amount')
                )
                ->leftJoin('donations', function ($join) use ($days) {
                    $join->on('campaigns.id', '=', 'donations.campaign_id')
                        ->where('donations.created_at', '>=', now()->subDays($days));
                })
                ->where('campaigns.status', 'published')
                ->groupBy('campaigns.id', 'campaigns.title', 'campaigns.charity_id', 'campaigns.campaign_type', 'campaigns.target_amount', 'campaigns.current_amount')
                ->having('donation_count', '>', 0)
                ->orderBy('donation_count', 'desc')
                ->orderBy('recent_amount', 'desc')
                ->limit($limit)
                ->with('charity:id,name')
                ->get()
                ->map(function ($campaign) {
                    return [
                        'id' => $campaign->id,
                        'title' => $campaign->title,
                        'charity' => optional($campaign->charity)->name ?? 'Unknown',
                        'campaign_type' => $campaign->campaign_type,
                        'target_amount' => (float) $campaign->target_amount,
                        'current_amount' => (float) $campaign->current_amount,
                        'donation_count' => (int) $campaign->donation_count,
                        'recent_amount' => (float) $campaign->recent_amount,
                        'progress' => $campaign->target_amount > 0 
                            ? round(($campaign->current_amount / $campaign->target_amount) * 100, 2)
                            : 0,
                    ];
                });
            
            return response()->json([
                'data' => $trending,
                'period_days' => (int) $days,
            ]);
        } catch (\Exception $e) {
            \Log::error('Trending campaigns error: ' . $e->getMessage() . ' | Stack: ' . $e->getTraceAsString());
            return response()->json([
                'data' => [],
                'period_days' => (int) $request->query('days', 30),
            ], 200);
        }
    }
    
    /**
     * GET /api/analytics/campaigns/{type}/stats
     * Statistics for a specific campaign type
     */
    public function campaignTypeStats($type, Request $request)
    {
        $charityId = $request->query('charity_id');
        
        $cacheKey = $charityId ? "analytics_type_{$type}_{$charityId}" : "analytics_type_{$type}_global";
        
        $stats = Cache::remember($cacheKey, 600, function () use ($type, $charityId) {
            $query = Campaign::where('campaign_type', $type)
                ->where('status', '!=', 'archived');
            
            if ($charityId) {
                $query->where('charity_id', $charityId);
            }
            
            // Basic stats
            $campaigns = $query->get();
            $totalCampaigns = $campaigns->count();
            
            if ($totalCampaigns === 0) {
                return [
                    'campaign_type' => $type,
                    'total_campaigns' => 0,
                    'funding' => [
                        'avg_goal' => 0,
                        'min_goal' => 0,
                        'max_goal' => 0,
                        'avg_raised' => 0,
                        'total_raised' => 0,
                    ],
                    'top_charities' => [],
                    'popular_locations' => [],
                ];
            }
            
            // Funding stats
            $goals = $campaigns->pluck('target_amount')->filter()->values();
            $raised = $campaigns->pluck('current_amount')->values();
            
            // Top charities
            $topCharities = Campaign::select('charity_id', DB::raw('COUNT(*) as campaign_count'))
                ->where('campaign_type', $type)
                ->where('status', '!=', 'archived')
                ->when($charityId, fn($q) => $q->where('charity_id', $charityId))
                ->groupBy('charity_id')
                ->orderBy('campaign_count', 'desc')
                ->limit(5)
                ->with('charity:id,name')
                ->get()
                ->map(fn($item) => [
                    'charity_id' => $item->charity_id,
                    'charity_name' => $item->charity->name ?? 'Unknown',
                    'campaign_count' => (int) $item->campaign_count,
                ]);
            
            // Popular locations (regions)
            $popularLocations = Campaign::select('region', DB::raw('COUNT(*) as count'))
                ->where('campaign_type', $type)
                ->whereNotNull('region')
                ->where('status', '!=', 'archived')
                ->when($charityId, fn($q) => $q->where('charity_id', $charityId))
                ->groupBy('region')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get()
                ->map(fn($item) => [
                    'region' => $item->region,
                    'count' => (int) $item->count,
                ]);
            
            return [
                'campaign_type' => $type,
                'total_campaigns' => $totalCampaigns,
                'funding' => [
                    'avg_goal' => $goals->count() > 0 ? round($goals->avg(), 2) : 0,
                    'min_goal' => $goals->count() > 0 ? (float) $goals->min() : 0,
                    'max_goal' => $goals->count() > 0 ? (float) $goals->max() : 0,
                    'avg_raised' => round($raised->avg(), 2),
                    'total_raised' => (float) $raised->sum(),
                ],
                'top_charities' => $topCharities,
                'popular_locations' => $popularLocations,
            ];
        });
        
        return response()->json($stats);
    }
    
    /**
     * GET /api/analytics/campaigns/{campaignId}/summary
     * Detailed summary for a specific campaign
     */
    public function campaignSummary($campaignId)
    {
        $campaign = Campaign::with('charity:id,name')->findOrFail($campaignId);
        
        // Donation stats
        $donations = Donation::where('campaign_id', $campaignId)
            ->select(
                DB::raw('COUNT(*) as total_donations'),
                DB::raw('COUNT(DISTINCT donor_id) as unique_donors'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN amount ELSE 0 END) as total_raised'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as pending_amount'),
                DB::raw('AVG(CASE WHEN status = "completed" THEN amount ELSE NULL END) as avg_donation')
            )
            ->first();
        
        // Timeline (donations over time - last 30 days)
        $timeline = Donation::where('campaign_id', $campaignId)
            ->where('status', 'completed')
            ->where('donated_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(donated_at) as date'), DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as amount'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date,
                'count' => (int) $item->count,
                'amount' => (float) $item->amount,
            ]);
        
        // Top donors
        $topDonors = Donation::where('campaign_id', $campaignId)
            ->where('status', 'completed')
            ->whereNotNull('donor_id')
            ->select('donor_id', DB::raw('SUM(amount) as total_donated'), DB::raw('COUNT(*) as donation_count'))
            ->groupBy('donor_id')
            ->orderBy('total_donated', 'desc')
            ->limit(10)
            ->with('donor:id,name')
            ->get()
            ->map(fn($item) => [
                'donor_id' => $item->donor_id,
                'donor_name' => $item->donor->name ?? 'Anonymous',
                'total_donated' => (float) $item->total_donated,
                'donation_count' => (int) $item->donation_count,
            ]);
        
        return response()->json([
            'campaign' => [
                'id' => $campaign->id,
                'title' => $campaign->title,
                'charity' => $campaign->charity->name ?? 'Unknown',
                'campaign_type' => $campaign->campaign_type,
                'status' => $campaign->status,
                'target_amount' => (float) $campaign->target_amount,
                'start_date' => $campaign->start_date?->format('Y-m-d'),
                'end_date' => $campaign->end_date?->format('Y-m-d'),
                'created_at' => $campaign->created_at->format('Y-m-d'),
            ],
            'statistics' => [
                'total_donations' => (int) $donations->total_donations,
                'unique_donors' => (int) $donations->unique_donors,
                'total_raised' => (float) $donations->total_raised,
                'pending_amount' => (float) $donations->pending_amount,
                'avg_donation' => round((float) $donations->avg_donation, 2),
                'progress' => $campaign->target_amount > 0 
                    ? round(($donations->total_raised / $campaign->target_amount) * 100, 2)
                    : 0,
            ],
            'timeline' => $timeline,
            'top_donors' => $topDonors,
        ]);
    }
    
    /**
     * GET /api/analytics/donors/{donorId}/summary
     * Donation history and aggregates for a donor
     */
    public function donorSummary($donorId, Request $request)
    {
        // Authorization: only the donor themselves or admin can view
        if ($request->user()->id !== (int) $donorId && !$request->user()->hasRole('admin')) {
            abort(403, 'Unauthorized to view this donor\'s data');
        }
        
        $donor = User::findOrFail($donorId);
        
        // Overall stats
        $stats = Donation::where('donor_id', $donorId)
            ->select(
                DB::raw('COUNT(*) as total_donations'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN amount ELSE 0 END) as total_donated'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as pending_amount'),
                DB::raw('AVG(CASE WHEN status = "completed" THEN amount ELSE NULL END) as avg_donation'),
                DB::raw('MIN(donated_at) as first_donation'),
                DB::raw('MAX(donated_at) as last_donation')
            )
            ->first();
        
        // By campaign type
        $byType = Donation::where('donations.donor_id', $donorId)
            ->where('donations.status', 'completed')
            ->join('campaigns', 'donations.campaign_id', '=', 'campaigns.id')
            ->select('campaigns.campaign_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(donations.amount) as total'))
            ->groupBy('campaigns.campaign_type')
            ->orderBy('total', 'desc')
            ->get()
            ->map(fn($item) => [
                'type' => $item->campaign_type,
                'label' => ucwords(str_replace('_', ' ', $item->campaign_type)),
                'count' => (int) $item->count,
                'total' => (float) $item->total,
            ]);
        
        // Recent donations
        $recentDonations = Donation::where('donor_id', $donorId)
            ->with(['campaign:id,title,campaign_type', 'charity:id,name'])
            ->orderBy('donated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($donation) => [
                'id' => $donation->id,
                'amount' => (float) $donation->amount,
                'status' => $donation->status,
                'campaign' => $donation->campaign ? [
                    'id' => $donation->campaign->id,
                    'title' => $donation->campaign->title,
                    'type' => $donation->campaign->campaign_type,
                ] : null,
                'charity' => $donation->charity->name ?? 'Unknown',
                'donated_at' => $donation->donated_at->format('Y-m-d H:i:s'),
            ]);
        
        // Monthly trend (last 12 months)
        $monthlyTrend = Donation::where('donor_id', $donorId)
            ->where('status', 'completed')
            ->where('donated_at', '>=', now()->subMonths(12))
            ->select(
                DB::raw('DATE_FORMAT(donated_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->map(fn($item) => [
                'month' => $item->month,
                'count' => (int) $item->count,
                'total' => (float) $item->total,
            ]);
        
        return response()->json([
            'donor' => [
                'id' => $donor->id,
                'name' => $donor->name,
                'email' => $donor->email,
            ],
            'statistics' => [
                'total_donations' => (int) $stats->total_donations,
                'total_donated' => (float) $stats->total_donated,
                'pending_amount' => (float) $stats->pending_amount,
                'avg_donation' => round((float) $stats->avg_donation, 2),
                'first_donation' => $stats->first_donation ? date('Y-m-d', strtotime($stats->first_donation)) : null,
                'last_donation' => $stats->last_donation ? date('Y-m-d', strtotime($stats->last_donation)) : null,
            ],
            'by_type' => $byType,
            'recent_donations' => $recentDonations,
            'monthly_trend' => $monthlyTrend,
        ]);
    }
    
    /**
     * GET /api/analytics/campaigns/{type}/advanced
     * Advanced analytics for a campaign type (histograms, percentiles, trends)
     */
    public function advancedTypeAnalytics($type, Request $request)
    {
        $charityId = $request->query('charity_id');
        
        $cacheKey = $charityId ? "analytics_advanced_{$type}_{$charityId}" : "analytics_advanced_{$type}_global";
        
        $data = Cache::remember($cacheKey, 600, function () use ($type, $charityId) {
            $query = Campaign::where('campaign_type', $type)
                ->where('status', '!=', 'archived');
            
            if ($charityId) {
                $query->where('charity_id', $charityId);
            }
            
            $campaigns = $query->get();
            
            if ($campaigns->isEmpty()) {
                return [
                    'campaign_type' => $type,
                    'fund_ranges' => [],
                    'percentiles' => [],
                    'top_beneficiaries' => [],
                    'trending_metrics' => null,
                ];
            }
            
            // Fund range histogram
            $targetAmounts = $campaigns->pluck('target_amount')->filter()->values();
            $histogram = $this->calculateHistogram($targetAmounts);
            
            // Percentiles
            $percentiles = $this->calculatePercentiles($targetAmounts, [10, 25, 50, 75, 90]);
            
            // Top beneficiaries (text frequency analysis)
            $beneficiaries = $campaigns->pluck('beneficiary')->filter();
            $topBeneficiaries = $this->extractTopBeneficiaries($beneficiaries);
            
            // Trending detection (week-over-week)
            $trendingMetrics = $this->calculateTrendingMetrics($type, $charityId);
            
            return [
                'campaign_type' => $type,
                'fund_ranges' => $histogram,
                'percentiles' => $percentiles,
                'top_beneficiaries' => $topBeneficiaries,
                'trending_metrics' => $trendingMetrics,
            ];
        });
        
        return response()->json($data);
    }
    
    /**
     * GET /api/analytics/trending-explanation/{type}
     * Detailed trending explanation for a campaign type
     */
    public function trendingExplanation($type)
    {
        $metrics = $this->calculateTrendingMetrics($type, null);
        
        if (!$metrics) {
            return response()->json([
                'explanation' => "No trending data available for this campaign type.",
                'is_trending' => false,
            ]);
        }
        
        $isTrending = $metrics['growth_percentage'] > 10;
        $direction = $metrics['growth_percentage'] > 0 ? 'increase' : 'decrease';
        
        $explanation = sprintf(
            "%s campaigns are %s with %d donation%s in the last 7 days (%+.1f%% vs previous week). " .
            "Average donation: ₱%s. This represents a %s in activity.",
            ucwords(str_replace('_', ' ', $type)),
            $isTrending ? 'trending' : 'stable',
            $metrics['current_week_donations'],
            $metrics['current_week_donations'] !== 1 ? 's' : '',
            $metrics['growth_percentage'],
            number_format($metrics['avg_donation'], 2),
            $direction
        );
        
        return response()->json([
            'explanation' => $explanation,
            'is_trending' => $isTrending,
            'metrics' => $metrics,
        ]);
    }
    
    /**
     * Calculate fund range histogram
     */
    private function calculateHistogram($amounts)
    {
        if ($amounts->isEmpty()) return [];
        
        $min = $amounts->min();
        $max = $amounts->max();
        $range = $max - $min;
        
        if ($range === 0) {
            return [[
                'range' => '₱' . number_format($min),
                'count' => $amounts->count(),
            ]];
        }
        
        // Create 5 bins
        $binSize = $range / 5;
        $bins = [];
        
        for ($i = 0; $i < 5; $i++) {
            $binStart = $min + ($i * $binSize);
            $binEnd = $min + (($i + 1) * $binSize);
            
            $count = $amounts->filter(function ($amount) use ($binStart, $binEnd, $i) {
                return $i === 4 
                    ? $amount >= $binStart && $amount <= $binEnd
                    : $amount >= $binStart && $amount < $binEnd;
            })->count();
            
            $bins[] = [
                'range' => '₱' . number_format($binStart) . ' - ₱' . number_format($binEnd),
                'count' => $count,
                'min' => (float) $binStart,
                'max' => (float) $binEnd,
            ];
        }
        
        return $bins;
    }
    
    /**
     * Calculate percentiles
     */
    private function calculatePercentiles($amounts, $percentiles)
    {
        if ($amounts->isEmpty()) return [];
        
        $sorted = $amounts->sort()->values();
        $result = [];
        
        foreach ($percentiles as $p) {
            $index = (($p / 100) * ($sorted->count() - 1));
            $lower = floor($index);
            $upper = ceil($index);
            
            if ($lower === $upper) {
                $value = $sorted[$lower];
            } else {
                $value = $sorted[$lower] + (($index - $lower) * ($sorted[$upper] - $sorted[$lower]));
            }
            
            $result[] = [
                'percentile' => $p,
                'value' => round($value, 2),
                'label' => "P{$p}",
            ];
        }
        
        return $result;
    }
    
    /**
     * Extract top beneficiaries from text (simple keyword extraction)
     */
    private function extractTopBeneficiaries($beneficiaries)
    {
        if ($beneficiaries->isEmpty()) return [];
        
        $keywords = [];
        
        foreach ($beneficiaries as $text) {
            // Extract meaningful words (>3 characters, common beneficiary terms)
            $words = preg_split('/[\s,\.]+/', strtolower($text));
            
            foreach ($words as $word) {
                $word = trim($word);
                
                // Filter out common stop words and short words
                $stopWords = ['the', 'and', 'for', 'with', 'from', 'will', 'this', 'that', 'who', 'are', 'can'];
                
                if (strlen($word) > 3 && !in_array($word, $stopWords) && !is_numeric($word)) {
                    if (!isset($keywords[$word])) {
                        $keywords[$word] = 0;
                    }
                    $keywords[$word]++;
                }
            }
        }
        
        arsort($keywords);
        
        return array_slice(array_map(function ($word, $count) {
            return [
                'term' => $word,
                'count' => $count,
            ];
        }, array_keys($keywords), $keywords), 0, 10);
    }
    
    /**
     * Calculate trending metrics (week-over-week)
     */
    private function calculateTrendingMetrics($type, $charityId = null)
    {
        // Current week (last 7 days)
        $currentWeekStart = now()->subDays(7);
        $currentWeekEnd = now();
        
        // Previous week (8-14 days ago)
        $previousWeekStart = now()->subDays(14);
        $previousWeekEnd = now()->subDays(7);
        
        // Get campaigns of this type
        $campaignIds = Campaign::where('campaign_type', $type)
            ->when($charityId, fn($q) => $q->where('charity_id', $charityId))
            ->pluck('id');
        
        if ($campaignIds->isEmpty()) {
            return null;
        }
        
        // Current week donations
        $currentWeek = Donation::whereIn('campaign_id', $campaignIds)
            ->where('status', 'completed')
            ->whereBetween('donated_at', [$currentWeekStart, $currentWeekEnd])
            ->select(
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total'),
                DB::raw('AVG(amount) as avg')
            )
            ->first();
        
        // Previous week donations
        $previousWeek = Donation::whereIn('campaign_id', $campaignIds)
            ->where('status', 'completed')
            ->whereBetween('donated_at', [$previousWeekStart, $previousWeekEnd])
            ->select(
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total')
            )
            ->first();
        
        $currentCount = $currentWeek->count ?? 0;
        $previousCount = $previousWeek->count ?? 0;
        
        $growthPercentage = $previousCount > 0 
            ? (($currentCount - $previousCount) / $previousCount) * 100
            : ($currentCount > 0 ? 100 : 0);
        
        return [
            'current_week_donations' => (int) $currentCount,
            'previous_week_donations' => (int) $previousCount,
            'current_week_amount' => (float) ($currentWeek->total ?? 0),
            'previous_week_amount' => (float) ($previousWeek->total ?? 0),
            'avg_donation' => (float) ($currentWeek->avg ?? 0),
            'growth_percentage' => round($growthPercentage, 1),
            'is_trending' => $growthPercentage > 10,
        ];
    }
    
    /**
     * GET /api/campaigns/filter
     * Filter campaigns with multiple criteria (for donors)
     */
    public function filterCampaigns(Request $request)
    {
        $validated = $request->validate([
            'campaign_type' => 'nullable|string',
            'region' => 'nullable|string',
            'province' => 'nullable|string',
            'city' => 'nullable|string',
            'min_goal' => 'nullable|numeric|min:0',
            'max_goal' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'nullable|in:published,closed',
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);
        
        $query = Campaign::with(['charity:id,name'])
            ->where('status', '!=', 'archived');
        
        // Filter by campaign type
        if (!empty($validated['campaign_type'])) {
            $query->where('campaign_type', $validated['campaign_type']);
        }
        
        // Filter by location
        if (!empty($validated['region'])) {
            $query->where('region', $validated['region']);
        }
        if (!empty($validated['province'])) {
            $query->where('province', $validated['province']);
        }
        if (!empty($validated['city'])) {
            $query->where('city', $validated['city']);
        }
        
        // Filter by goal range
        if (isset($validated['min_goal'])) {
            $query->where('target_amount', '>=', $validated['min_goal']);
        }
        if (isset($validated['max_goal'])) {
            $query->where('target_amount', '<=', $validated['max_goal']);
        }
        
        // Filter by date range
        if (!empty($validated['start_date'])) {
            $query->where('start_date', '>=', $validated['start_date']);
        }
        if (!empty($validated['end_date'])) {
            $query->where('end_date', '<=', $validated['end_date']);
        }
        
        // Filter by status
        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        } else {
            // Default to published only
            $query->where('status', 'published');
        }
        
        // Search in title and description
        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('beneficiary', 'LIKE', "%{$search}%");
            });
        }
        
        // Order by latest first
        $query->orderBy('created_at', 'desc');
        
        // Paginate results
        $perPage = $validated['per_page'] ?? 12;
        $campaigns = $query->paginate($perPage);
        
        // Add current_amount to each campaign
        $campaigns->getCollection()->transform(function ($campaign) {
            return [
                'id' => $campaign->id,
                'title' => $campaign->title,
                'description' => $campaign->description,
                'campaign_type' => $campaign->campaign_type,
                'target_amount' => (float) $campaign->target_amount,
                'current_amount' => (float) $campaign->current_amount,
                'progress' => $campaign->target_amount > 0 
                    ? round(($campaign->current_amount / $campaign->target_amount) * 100, 2)
                    : 0,
                'status' => $campaign->status,
                'start_date' => $campaign->start_date?->format('Y-m-d'),
                'end_date' => $campaign->end_date?->format('Y-m-d'),
                'region' => $campaign->region,
                'province' => $campaign->province,
                'city' => $campaign->city,
                'charity' => [
                    'id' => $campaign->charity->id,
                    'name' => $campaign->charity->name,
                ],
                'cover_image_path' => $campaign->cover_image_path,
                'created_at' => $campaign->created_at->format('Y-m-d'),
            ];
        });
        
        return response()->json($campaigns);
    }
    
    /**
     * GET /api/analytics/growth-by-type
     * Get donation growth rate by campaign type (month-over-month)
     */
    public function growthByType(Request $request)
    {
        $charityId = $request->query('charity_id');
        
        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();
        $previousMonthEnd = now()->subMonth()->endOfMonth();
        
        $types = ['education', 'feeding_program', 'medical', 'disaster_relief', 'environment', 'animal_welfare', 'other'];
        $growthData = [];
        
        foreach ($types as $type) {
            // Get campaigns of this type
            $campaignIds = Campaign::where('campaign_type', $type)
                ->when($charityId, fn($q) => $q->where('charity_id', $charityId))
                ->pluck('id');
            
            if ($campaignIds->isEmpty()) continue;
            
            // Current month donations
            $currentMonthDonations = Donation::whereIn('campaign_id', $campaignIds)
                ->where('status', 'completed')
                ->where('donated_at', '>=', $currentMonth)
                ->select(
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(amount) as total')
                )
                ->first();
            
            // Previous month donations
            $previousMonthDonations = Donation::whereIn('campaign_id', $campaignIds)
                ->where('status', 'completed')
                ->whereBetween('donated_at', [$previousMonth, $previousMonthEnd])
                ->select(
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(amount) as total')
                )
                ->first();
            
            $currentTotal = (float) ($currentMonthDonations->total ?? 0);
            $previousTotal = (float) ($previousMonthDonations->total ?? 0);
            $currentCount = (int) ($currentMonthDonations->count ?? 0);
            $previousCount = (int) ($previousMonthDonations->count ?? 0);
            
            $growthPercentage = $previousTotal > 0 
                ? (($currentTotal - $previousTotal) / $previousTotal) * 100
                : ($currentTotal > 0 ? 100 : 0);
            
            $countGrowth = $previousCount > 0 
                ? (($currentCount - $previousCount) / $previousCount) * 100
                : ($currentCount > 0 ? 100 : 0);
            
            $growthData[] = [
                'type' => $type,
                'label' => ucwords(str_replace('_', ' ', $type)),
                'current_month_total' => $currentTotal,
                'previous_month_total' => $previousTotal,
                'current_month_count' => $currentCount,
                'previous_month_count' => $previousCount,
                'growth_percentage' => round($growthPercentage, 1),
                'count_growth_percentage' => round($countGrowth, 1),
            ];
        }
        
        // Sort by growth percentage
        usort($growthData, fn($a, $b) => $b['growth_percentage'] <=> $a['growth_percentage']);
        
        return response()->json([
            'data' => $growthData,
            'period' => [
                'current_month' => $currentMonth->format('Y-m'),
                'previous_month' => $previousMonth->format('Y-m'),
            ],
        ]);
    }
    
    /**
     * GET /api/analytics/most-improved
     * Find the campaign with highest month-over-month growth
     */
    public function mostImprovedCampaign(Request $request)
    {
        $charityId = $request->query('charity_id');
        
        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();
        $previousMonthEnd = now()->subMonth()->endOfMonth();
        
        $campaigns = Campaign::where('status', 'published')
            ->when($charityId, fn($q) => $q->where('charity_id', $charityId))
            ->with('charity:id,name')
            ->get();
        
        $improvedCampaigns = [];
        
        foreach ($campaigns as $campaign) {
            // Current month donations
            $currentMonthDonations = Donation::where('campaign_id', $campaign->id)
                ->where('status', 'completed')
                ->where('donated_at', '>=', $currentMonth)
                ->select(
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(amount) as total')
                )
                ->first();
            
            // Previous month donations
            $previousMonthDonations = Donation::where('campaign_id', $campaign->id)
                ->where('status', 'completed')
                ->whereBetween('donated_at', [$previousMonth, $previousMonthEnd])
                ->select(
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(amount) as total')
                )
                ->first();
            
            $currentTotal = (float) ($currentMonthDonations->total ?? 0);
            $previousTotal = (float) ($previousMonthDonations->total ?? 0);
            
            // Only include campaigns with donations in both periods
            if ($previousTotal > 0 && $currentTotal > 0) {
                $growthPercentage = (($currentTotal - $previousTotal) / $previousTotal) * 100;
                
                $improvedCampaigns[] = [
                    'id' => $campaign->id,
                    'title' => $campaign->title,
                    'campaign_type' => $campaign->campaign_type,
                    'charity' => $campaign->charity->name ?? 'Unknown',
                    'current_month_total' => $currentTotal,
                    'previous_month_total' => $previousTotal,
                    'growth_percentage' => round($growthPercentage, 1),
                    'growth_amount' => $currentTotal - $previousTotal,
                ];
            }
        }
        
        // Sort by growth percentage and get top
        usort($improvedCampaigns, fn($a, $b) => $b['growth_percentage'] <=> $a['growth_percentage']);
        
        return response()->json([
            'data' => array_slice($improvedCampaigns, 0, 1)[0] ?? null,
            'period' => [
                'current_month' => $currentMonth->format('Y-m'),
                'previous_month' => $previousMonth->format('Y-m'),
            ],
        ]);
    }
    
    /**
     * GET /api/analytics/activity-timeline
     * Get daily/weekly campaign and donation activity
     */
    public function activityTimeline(Request $request)
    {
        $charityId = $request->query('charity_id');
        $days = $request->query('days', 30);
        $groupBy = $request->query('group_by', 'day'); // day or week
        
        $startDate = now()->subDays($days)->startOfDay();
        
        if ($groupBy === 'week') {
            // Weekly grouping
            $campaignActivity = Campaign::where('created_at', '>=', $startDate)
                ->when($charityId, fn($q) => $q->where('charity_id', $charityId))
                ->select(
                    DB::raw('YEARWEEK(created_at) as period'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('MIN(DATE(created_at)) as date')
                )
                ->groupBy('period')
                ->orderBy('period')
                ->get();
            
            $donationActivity = Donation::where('donations.donated_at', '>=', $startDate)
                ->where('donations.status', 'completed')
                ->when($charityId, function($q) use ($charityId) {
                    $q->join('campaigns', 'donations.campaign_id', '=', 'campaigns.id')
                      ->where('campaigns.charity_id', $charityId);
                })
                ->select(
                    DB::raw('YEARWEEK(donations.donated_at) as period'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(donations.amount) as total'),
                    DB::raw('MIN(DATE(donations.donated_at)) as date')
                )
                ->groupBy('period')
                ->orderBy('period')
                ->get();
        } else {
            // Daily grouping
            $campaignActivity = Campaign::where('created_at', '>=', $startDate)
                ->when($charityId, fn($q) => $q->where('charity_id', $charityId))
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            $donationActivity = Donation::where('donations.donated_at', '>=', $startDate)
                ->where('donations.status', 'completed')
                ->when($charityId, function($q) use ($charityId) {
                    $q->join('campaigns', 'donations.campaign_id', '=', 'campaigns.id')
                      ->where('campaigns.charity_id', $charityId);
                })
                ->select(
                    DB::raw('DATE(donations.donated_at) as date'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(donations.amount) as total')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        }
        
        // Merge data by date
        $timeline = [];
        $dateMap = [];
        
        foreach ($campaignActivity as $item) {
            $dateKey = $item->date;
            if (!isset($dateMap[$dateKey])) {
                $dateMap[$dateKey] = [
                    'date' => $dateKey,
                    'campaigns_created' => 0,
                    'donations_received' => 0,
                    'donation_amount' => 0,
                ];
            }
            $dateMap[$dateKey]['campaigns_created'] = (int) $item->count;
        }
        
        foreach ($donationActivity as $item) {
            $dateKey = $item->date;
            if (!isset($dateMap[$dateKey])) {
                $dateMap[$dateKey] = [
                    'date' => $dateKey,
                    'campaigns_created' => 0,
                    'donations_received' => 0,
                    'donation_amount' => 0,
                ];
            }
            $dateMap[$dateKey]['donations_received'] = (int) $item->count;
            $dateMap[$dateKey]['donation_amount'] = (float) $item->total;
        }
        
        $timeline = array_values($dateMap);
        usort($timeline, fn($a, $b) => strcmp($a['date'], $b['date']));
        
        return response()->json([
            'data' => $timeline,
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
                'days' => $days,
                'group_by' => $groupBy,
            ],
        ]);
    }
    
    /**
     * GET /api/campaigns/filter-options
     * Get available filter options (regions, types, etc.)
     */
    public function filterOptions()
    {
        $cacheKey = 'campaign_filter_options';
        
        $options = Cache::remember($cacheKey, 3600, function () {
            // Get unique regions
            $regions = Campaign::whereNotNull('region')
                ->where('status', 'published')
                ->distinct()
                ->pluck('region')
                ->filter()
                ->sort()
                ->values();
            
            // Get unique provinces
            $provinces = Campaign::whereNotNull('province')
                ->where('status', 'published')
                ->distinct()
                ->pluck('province')
                ->filter()
                ->sort()
                ->values();
            
            // Get unique cities
            $cities = Campaign::whereNotNull('city')
                ->where('status', 'published')
                ->distinct()
                ->pluck('city')
                ->filter()
                ->sort()
                ->values();
            
            // Campaign types
            $types = [
                ['value' => 'education', 'label' => 'Education'],
                ['value' => 'feeding_program', 'label' => 'Feeding Program'],
                ['value' => 'medical', 'label' => 'Medical'],
                ['value' => 'disaster_relief', 'label' => 'Disaster Relief'],
                ['value' => 'environment', 'label' => 'Environment'],
                ['value' => 'animal_welfare', 'label' => 'Animal Welfare'],
                ['value' => 'other', 'label' => 'Other'],
            ];
            
            // Goal ranges (predefined)
            $goalRanges = [
                ['label' => 'Under ₱10,000', 'min' => 0, 'max' => 10000],
                ['label' => '₱10,000 - ₱50,000', 'min' => 10000, 'max' => 50000],
                ['label' => '₱50,000 - ₱100,000', 'min' => 50000, 'max' => 100000],
                ['label' => '₱100,000 - ₱500,000', 'min' => 100000, 'max' => 500000],
                ['label' => 'Over ₱500,000', 'min' => 500000, 'max' => null],
            ];
            
            return [
                'regions' => $regions,
                'provinces' => $provinces,
                'cities' => $cities,
                'types' => $types,
                'goal_ranges' => $goalRanges,
            ];
        });
        
        return response()->json($options);
    }
    
    /**
     * GET /api/analytics/summary
     * Overall summary metrics
     */
    public function summary(Request $request)
    {
        try {
            $charityId = $this->getCharityId($request);
            
            // IMPORTANT: Always filter by charity for data privacy
            if (!$charityId) {
                return response()->json(['data' => [
                    'total_campaigns' => 0,
                    'active_campaigns' => 0,
                    'total_raised' => 0,
                    'total_donations' => 0,
                    'avg_campaign_amount' => 0,
                ]]);
            }
            
            $query = Campaign::where('charity_id', $charityId);
            
            $totalCampaigns = $query->count();
            $activeCampaigns = (clone $query)->where('status', 'published')->count();
            $totalRaised = (clone $query)->sum('current_amount') ?? 0;
            $totalDonations = Donation::where('status', 'completed')
                ->whereHas('campaign', function($cq) use ($charityId) {
                    $cq->where('charity_id', $charityId);
                })
                ->count();
            
            return response()->json([
                'data' => [
                    'total_campaigns' => $totalCampaigns,
                    'active_campaigns' => $activeCampaigns,
                    'total_raised' => (float) $totalRaised,
                    'total_donations' => $totalDonations,
                    'avg_campaign_amount' => $totalCampaigns > 0 ? (float) ($totalRaised / $totalCampaigns) : 0,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Summary analytics error: ' . $e->getMessage());
            return response()->json([
                'data' => [
                    'total_campaigns' => 0,
                    'active_campaigns' => 0,
                    'total_raised' => 0,
                    'total_donations' => 0,
                    'avg_campaign_amount' => 0,
                ]
            ], 200);
        }
    }
    
    /**
     * GET /api/analytics/campaigns/locations
     * Campaign distribution by location with raised amounts
     */
    public function campaignLocations(Request $request)
    {
        try {
            $charityId = $this->getCharityId($request);
            
            // IMPORTANT: Always filter by charity for data privacy
            if (!$charityId) {
                return response()->json([]);
            }
            
            // Get all campaigns with their location and raised amounts
            $query = Campaign::select(
                    'campaigns.id',
                    'campaigns.city',
                    'campaigns.province',
                    'campaigns.region',
                    'campaigns.title',
                    DB::raw('COALESCE(SUM(CASE WHEN donations.status = "completed" THEN donations.amount ELSE 0 END), 0) as raised_amount')
                )
                ->leftJoin('donations', 'campaigns.id', '=', 'donations.campaign_id')
                ->where('campaigns.status', '!=', 'archived')
                ->whereNotNull('campaigns.city')
                ->where('campaigns.city', '!=', '')
                ->where('campaigns.charity_id', $charityId)
                ->groupBy(
                    'campaigns.id',
                    'campaigns.city',
                    'campaigns.province',
                    'campaigns.region',
                    'campaigns.title'
                )
                ->get()
                ->map(function($campaign) {
                    return [
                        'id' => $campaign->id,
                        'city' => $campaign->city,
                        'province' => $campaign->province,
                        'region' => $campaign->region,
                        'title' => $campaign->title,
                        'raised_amount' => (float) $campaign->raised_amount,
                    ];
                });
            
            \Log::info('Location data count:', ['count' => $query->count()]);
            
            return response()->json($query);
        } catch (\Exception $e) {
            \Log::error('Campaign locations error: ' . $e->getMessage() . ' | Stack: ' . $e->getTraceAsString());
            return response()->json([], 200);
        }
    }
    
    /**
     * GET /api/analytics/campaigns/by-location
     * Campaign distribution with location hierarchy filtering
     */
    public function getCampaignsByLocation(Request $request)
    {
        try {
            $charityId = $request->query('charity_id');
            $region = $request->query('region');
            $province = $request->query('province');
            $city = $request->query('city');
            
            $query = Campaign::select('region', 'province', 'city', 'barangay', DB::raw('COUNT(*) as total'))
                ->where('status', '!=', 'archived');
            
            if ($charityId) {
                $query->where('charity_id', $charityId);
            }
            
            if ($region) {
                $query->where('region', $region);
            }
            
            if ($province) {
                $query->where('province', $province);
            }
            
            if ($city) {
                $query->where('city', $city);
            }
            
            // Filter out null values
            $query->whereNotNull('city')
                ->where('city', '!=', '');
            
            $data = $query->groupBy('region', 'province', 'city', 'barangay')
                ->orderByDesc('total')
                ->limit(50)
                ->get()
                ->map(function($item) {
                    return [
                        'region' => $item->region,
                        'province' => $item->province,
                        'city' => $item->city,
                        'barangay' => $item->barangay,
                        'total' => (int) $item->total,
                    ];
                });
            
            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Campaigns by location error: ' . $e->getMessage());
            return response()->json([], 200);
        }
    }
    
    /**
     * GET /api/analytics/campaigns/location-summary
     * Summary statistics for location analytics
     */
    public function getLocationSummary(Request $request)
    {
        try {
            $charityId = $request->query('charity_id');
            
            $query = Campaign::where('status', '!=', 'archived');
            
            if ($charityId) {
                $query->where('charity_id', $charityId);
            }
            
            $regionCount = (clone $query)->whereNotNull('region')->distinct('region')->count('region');
            $provinceCount = (clone $query)->whereNotNull('province')->distinct('province')->count('province');
            $cityCount = (clone $query)->whereNotNull('city')->distinct('city')->count('city');
            $totalCampaigns = $query->count();
            
            return response()->json([
                'regions' => $regionCount,
                'provinces' => $provinceCount,
                'cities' => $cityCount,
                'campaigns' => $totalCampaigns,
            ]);
        } catch (\Exception $e) {
            \Log::error('Location summary error: ' . $e->getMessage());
            return response()->json([
                'regions' => 0,
                'provinces' => 0,
                'cities' => 0,
                'campaigns' => 0,
            ], 200);
        }
    }
    
    /**
     * GET /api/analytics/campaigns/location-filters
     * Get available regions, provinces, and cities for filtering
     */
    public function getLocationFilters(Request $request)
    {
        try {
            $charityId = $request->query('charity_id');
            $region = $request->query('region');
            $province = $request->query('province');
            
            $query = Campaign::where('status', '!=', 'archived');
            
            if ($charityId) {
                $query->where('charity_id', $charityId);
            }
            
            // Get regions
            $regions = (clone $query)
                ->whereNotNull('region')
                ->where('region', '!=', '')
                ->distinct()
                ->pluck('region')
                ->sort()
                ->values();
            
            // Get provinces (filtered by region if provided)
            $provinceQuery = clone $query;
            if ($region) {
                $provinceQuery->where('region', $region);
            }
            $provinces = $provinceQuery
                ->whereNotNull('province')
                ->where('province', '!=', '')
                ->distinct()
                ->pluck('province')
                ->sort()
                ->values();
            
            // Get cities (filtered by province if provided)
            $cityQuery = clone $query;
            if ($region) {
                $cityQuery->where('region', $region);
            }
            if ($province) {
                $cityQuery->where('province', $province);
            }
            $cities = $cityQuery
                ->whereNotNull('city')
                ->where('city', '!=', '')
                ->distinct()
                ->pluck('city')
                ->sort()
                ->values();
            
            return response()->json([
                'regions' => $regions,
                'provinces' => $provinces,
                'cities' => $cities,
            ]);
        } catch (\Exception $e) {
            \Log::error('Location filters error: ' . $e->getMessage());
            return response()->json([
                'regions' => [],
                'provinces' => [],
                'cities' => [],
            ], 200);
        }
    }
    
    /**
     * GET /api/analytics/overview
     * Overview summary metrics for charity analytics dashboard
     */
    public function getOverviewSummary(Request $request)
    {
        try {
            $charityId = $this->getCharityId($request);
            
            // IMPORTANT: Always filter by charity for data privacy
            if (!$charityId) {
                return response()->json([
                    'total_campaigns' => 0,
                    'total_raised' => 0,
                    'average_donation' => 0,
                    'average_goal_achievement' => 0,
                    'total_verified_donations' => 0,
                    'campaigns_by_type' => [],
                    'top_campaign' => null,
                    'recent_activity' => []
                ]);
            }
            
            // Get campaigns query
            $campaignsQuery = Campaign::where('status', '!=', 'archived');
            if ($charityId) {
                $campaignsQuery->where('charity_id', $charityId);
            }
            $campaigns = $campaignsQuery->get();
            
            // Get verified donations for these campaigns
            $donations = Donation::whereIn('campaign_id', $campaigns->pluck('id'))
                ->where('verification_status', 'verified')
                ->get();
            
            // Calculate metrics
            $totalCampaigns = $campaigns->count();
            $totalRaised = $donations->sum('amount');
            $averageDonation = $donations->count() > 0 ? round($totalRaised / $donations->count(), 2) : 0;
            
            // Calculate average goal achievement
            $goalAchievements = $campaigns->map(function ($campaign) {
                $goal = max($campaign->goal_amount, 1);
                return ($campaign->raised_amount / $goal) * 100;
            });
            $averageGoalAchievement = $campaigns->count() > 0 ? round($goalAchievements->avg(), 1) : 0;
            
            $totalVerifiedDonations = $donations->count();
            $activeCampaigns = $campaigns->where('status', 'active')->count();
            $completedCampaigns = $campaigns->where('status', 'completed')->count();
            
            // Get top performing campaign
            $topCampaign = $campaigns
                ->sortByDesc(function ($campaign) {
                    $goal = max($campaign->goal_amount, 1);
                    return $campaign->raised_amount / $goal;
                })
                ->first();
            
            return response()->json([
                'total_campaigns' => $totalCampaigns,
                'total_raised' => (float) $totalRaised,
                'average_donation' => (float) $averageDonation,
                'average_goal_achievement' => (float) $averageGoalAchievement,
                'total_verified_donations' => $totalVerifiedDonations,
                'active_campaigns' => $activeCampaigns,
                'completed_campaigns' => $completedCampaigns,
                'top_campaign' => $topCampaign ? [
                    'title' => $topCampaign->title,
                    'progress' => round(($topCampaign->raised_amount / max($topCampaign->goal_amount, 1)) * 100, 1),
                    'raised_amount' => (float) $topCampaign->raised_amount,
                    'goal_amount' => (float) $topCampaign->goal_amount,
                ] : null,
            ]);
        } catch (\Exception $e) {
            \Log::error('Overview summary error: ' . $e->getMessage());
            return response()->json([
                'total_campaigns' => 0,
                'total_raised' => 0,
                'average_donation' => 0,
                'average_goal_achievement' => 0,
                'total_verified_donations' => 0,
                'active_campaigns' => 0,
                'completed_campaigns' => 0,
                'top_campaign' => null,
            ], 200);
        }
    }
    
    /**
     * GET /api/analytics/trends
     * Monthly trends and timing analytics
     */
    public function getTrendsData(Request $request)
    {
        try {
            $charityId = $request->query('charity_id');
            $months = $request->query('months', 12);
            
            $user = $request->user();
            
            // If no charity_id provided, find the charity owned by this user
            if (!$charityId && $user) {
                $charity = \App\Models\Charity::where('owner_id', $user->id)->first();
                if ($charity) {
                    $charityId = $charity->id;
                }
            }
            
            // Get actual date range from data
            $firstCampaign = Campaign::when($charityId, fn($q) => $q->where('charity_id', $charityId))
                ->where('status', '!=', 'archived')
                ->orderBy('created_at', 'asc')
                ->first();
            
            $firstDonation = Donation::when($charityId, fn($q) => $q->whereHas('campaign', fn($c) => $c->where('charity_id', $charityId)))
                ->where('status', 'completed')
                ->orderBy('created_at', 'asc')
                ->first();
            
            // Use the earliest date or default to 12 months
            $startDate = null;
            if ($firstCampaign || $firstDonation) {
                $campaignDate = $firstCampaign ? $firstCampaign->created_at : null;
                $donationDate = $firstDonation ? $firstDonation->created_at : null;
                
                if ($campaignDate && $donationDate) {
                    $startDate = $campaignDate < $donationDate ? $campaignDate : $donationDate;
                } else {
                    $startDate = $campaignDate ?? $donationDate;
                }
            }
            
            // Calculate months between start and now, minimum 12, maximum 60 (5 years)
            if ($startDate) {
                $monthsDiff = now()->diffInMonths($startDate) + 1;
                $months = min(max($monthsDiff, 12), 60);
            }
            
            // Count actual data for this charity
            $testCount = Campaign::where('charity_id', $charityId)
                ->where('status', '!=', 'archived')
                ->count();
            
            $testDonationCount = Donation::whereHas('campaign', fn($q) => $q->where('charity_id', $charityId))
                ->where('status', 'completed')
                ->count();
            
            \Log::info('Trends query setup', [
                'charity_id' => $charityId,
                'campaigns_found' => $testCount,
                'donations_found' => $testDonationCount,
                'first_campaign_date' => $firstCampaign?->created_at,
                'first_donation_date' => $firstDonation?->created_at,
                'months_to_show' => $months,
            ]);
            
            // Campaign Activity - Group campaigns by month directly from database
            $campaignActivity = Campaign::selectRaw("DATE_FORMAT(created_at, '%b %Y') as month, COUNT(*) as count")
                ->where('status', '!=', 'archived')
                ->when($charityId, fn($q) => $q->where('charity_id', $charityId))
                ->groupBy('month')
                ->orderByRaw("DATE_FORMAT(created_at, '%Y-%m')")
                ->get()
                ->toArray();
            
            // If no data, return empty structure
            if (empty($campaignActivity)) {
                $campaignActivity = [];
            }
            
            // Donation Trends - Group donations by month directly from database
            $donationTrendsRaw = Donation::selectRaw("DATE_FORMAT(donations.created_at, '%b %Y') as month, SUM(amount) as amount")
                ->join('campaigns', 'donations.campaign_id', '=', 'campaigns.id')
                ->where('donations.status', 'completed')
                ->when($charityId, fn($q) => $q->where('campaigns.charity_id', $charityId))
                ->groupBy('month')
                ->orderByRaw("DATE_FORMAT(donations.created_at, '%Y-%m')")
                ->get();
            
            $donationTrends = $donationTrendsRaw->map(function($item) {
                return [
                    'month' => $item->month,
                    'amount' => (float) $item->amount,
                ];
            })->toArray();
            
            // Calculate cumulative growth
            $cumulativeGrowth = [];
            $cumulativeTotal = 0;
            foreach ($donationTrends as $trend) {
                $cumulativeTotal += $trend['amount'];
                $cumulativeGrowth[] = [
                    'month' => $trend['month'],
                    'totalRaised' => (float) $cumulativeTotal,
                ];
            }
            
            // Calculate Summary Statistics
            $summary = $this->calculateTrendsSummary($charityId, $months);
            
            // Generate Insights
            $insights = $this->generateTrendsInsights($campaignActivity, $donationTrends, $summary);
            
            \Log::info('Trends data response:', [
                'campaign_activity_count' => count($campaignActivity),
                'campaign_activity' => $campaignActivity,
                'donation_trends_count' => count($donationTrends),
                'donation_trends' => $donationTrends,
                'summary' => $summary,
                'insights_count' => count($insights),
            ]);
            
            return response()->json([
                'campaign_activity' => $campaignActivity,
                'donation_trends' => $donationTrends,
                'cumulative_growth' => $cumulativeGrowth,
                'summary' => $summary,
                'insights' => $insights,
            ]);
        } catch (\Exception $e) {
            \Log::error('Trends data error: ' . $e->getMessage() . ' | Stack: ' . $e->getTraceAsString());
            return response()->json([
                'campaign_activity' => [],
                'donation_trends' => [],
                'cumulative_growth' => [],
                'summary' => [
                    'busiest_month' => 'N/A',
                    'most_donations' => 'N/A',
                    'avg_duration' => 0,
                    'fastest_growth' => 'N/A',
                ],
                'insights' => [],
            ], 200);
        }
    }
    
    /**
     * Calculate summary statistics for trends
     */
    private function calculateTrendsSummary($charityId, $months)
    {
        // Busiest month (most campaigns created) - use all data
        $busiestMonth = Campaign::select(
                DB::raw('DATE_FORMAT(created_at, "%M %Y") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('status', '!=', 'archived')
            ->when($charityId, fn($q) => $q->where('charity_id', $charityId))
            ->groupBy('month')
            ->orderByDesc('count')
            ->first();
        
        // Month with most donations - use all data
        $mostDonationsMonth = Donation::select(
                DB::raw('DATE_FORMAT(created_at, "%M %Y") as month'),
                DB::raw('SUM(amount) as total')
            )
            ->where('status', 'completed')
            ->when($charityId, fn($q) => $q->whereHas('campaign', fn($c) => $c->where('charity_id', $charityId)))
            ->groupBy('month')
            ->orderByDesc('total')
            ->first();
        
        // Average campaign duration - use all data
        $avgDurationData = Campaign::where('status', '!=', 'archived')
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->when($charityId, fn($q) => $q->where('charity_id', $charityId))
            ->selectRaw('AVG(DATEDIFF(end_date, start_date)) as avg_days, COUNT(*) as count')
            ->first();
        
        $avgDuration = ($avgDurationData && $avgDurationData->count > 0) 
            ? round((float) $avgDurationData->avg_days) 
            : 0;
        
        // Fastest growing month (biggest donation increase) - use all data
        $fastestGrowth = 'N/A';
        $growthData = Donation::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as year_month'),
                DB::raw('DATE_FORMAT(created_at, "%M %Y") as month_label'),
                DB::raw('SUM(amount) as total')
            )
            ->where('status', 'completed')
            ->when($charityId, fn($q) => $q->whereHas('campaign', fn($c) => $c->where('charity_id', $charityId)))
            ->groupBy('year_month', 'month_label')
            ->orderBy('year_month')
            ->get();
        
        if ($growthData->count() >= 2) {
            $maxGrowth = 0;
            for ($i = 1; $i < $growthData->count(); $i++) {
                $prev = $growthData[$i - 1]->total;
                $current = $growthData[$i]->total;
                $growth = $prev > 0 ? (($current - $prev) / $prev) * 100 : 0;
                
                if ($growth > $maxGrowth) {
                    $maxGrowth = $growth;
                    $fastestGrowth = $growthData[$i]->month_label;
                }
            }
        }
        
        return [
            'busiest_month' => $busiestMonth->month ?? 'N/A',
            'most_donations' => $mostDonationsMonth->month ?? 'N/A',
            'avg_duration' => round((float) $avgDuration),
            'fastest_growth' => $fastestGrowth,
        ];
    }
    
    /**
     * Generate insights from trends data
     */
    private function generateTrendsInsights($campaignActivity, $donationTrends, $summary)
    {
        $insights = [];
        
        // Campaign activity insight
        if (!empty($campaignActivity)) {
            $maxActivity = collect($campaignActivity)->max('count');
            $maxMonth = collect($campaignActivity)->firstWhere('count', $maxActivity);
            if ($maxMonth && $maxMonth['count'] > 0) {
                $insights[] = "{$maxMonth['month']} was the most active month with {$maxMonth['count']} campaign(s) created.";
            }
        }
        
        // Donation trend insight
        if (!empty($donationTrends)) {
            $maxDonation = collect($donationTrends)->max('amount');
            $maxMonth = collect($donationTrends)->firstWhere('amount', $maxDonation);
            if ($maxMonth && $maxMonth['amount'] > 0) {
                $insights[] = "Donations peaked in {$maxMonth['month']} with ₱" . number_format($maxMonth['amount'], 2) . " raised.";
            }
        }
        
        // Growth insight
        if ($summary['fastest_growth'] !== 'N/A') {
            $insights[] = "{$summary['fastest_growth']} showed the fastest donation growth rate.";
        }
        
        // Default insight if none generated
        if (empty($insights)) {
            $insights[] = "Continue tracking your campaign and donation trends for valuable insights.";
        }
        
        return $insights;
    }
    
    /**
     * GET /api/analytics/campaigns/beneficiaries
     * Campaign distribution by beneficiary group
     */
    public function getCampaignBeneficiaryDistribution(Request $request)
    {
        try {
            $charityId = $this->getCharityId($request);
            
            // IMPORTANT: Always filter by charity for data privacy
            if (!$charityId) {
                return response()->json([]);
            }
            
            $query = Campaign::select('beneficiary_group', DB::raw('COUNT(*) as total'))
                ->where('status', '!=', 'archived')
                ->whereNotNull('beneficiary_group')
                ->where('beneficiary_group', '!=', '');
            
            if ($charityId) {
                $query->where('charity_id', $charityId);
            }
            
            $data = $query->groupBy('beneficiary_group')
                ->orderByDesc('total')
                ->limit(10)
                ->get()
                ->map(function($item) {
                    return [
                        'beneficiary_group' => $item->beneficiary_group,
                        'total' => (int) $item->total,
                        'label' => $item->beneficiary_group,
                        'count' => (int) $item->total,
                    ];
                });
            
            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Beneficiary distribution error: ' . $e->getMessage());
            return response()->json([], 200);
        }
    }
    
    /**
     * GET /api/analytics/campaigns/temporal
     * Monthly campaign and donation trends
     */
    public function temporalTrends(Request $request)
    {
        try {
            $charityId = $this->getCharityId($request);
            
            // IMPORTANT: Always filter by charity for data privacy
            if (!$charityId) {
                return response()->json(['data' => []]);
            }
            
            $months = $request->query('months', 6);
            
            $trends = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $monthStart = now()->subMonths($i)->startOfMonth();
                $monthEnd = now()->subMonths($i)->endOfMonth();
                $period = $monthStart->format('M'); // Jan, Feb, Mar format
                
                $campaignQuery = Campaign::whereBetween('created_at', [$monthStart, $monthEnd])
                    ->where('status', '!=', 'archived')
                    ->where('charity_id', $charityId);
                
                $donationQuery = Donation::whereBetween('created_at', [$monthStart, $monthEnd])
                    ->where('status', 'completed')
                    ->whereHas('campaign', function($q) use ($charityId) {
                        $q->where('charity_id', $charityId);
                    });
                
                // Clone queries to avoid reusing same instance
                $campaignCount = (clone $campaignQuery)->count();
                $donationCount = (clone $donationQuery)->count();
                $donationAmount = (clone $donationQuery)->sum('amount') ?? 0;
                
                $trends[] = [
                    'period' => $period,
                    'campaign_count' => $campaignCount,
                    'donation_count' => $donationCount,
                    'total_amount' => (float) $donationAmount,
                ];
            }
            
            return response()->json(['data' => $trends]);
        } catch (\Exception $e) {
            \Log::error('Temporal trends error: ' . $e->getMessage() . ' | Stack: ' . $e->getTraceAsString());
            return response()->json(['data' => []], 200);
        }
    }
    
    /**
     * GET /api/analytics/campaigns/fund-ranges
     * Distribution of campaigns by funding goal ranges
     */
    public function fundRanges(Request $request)
    {
        try {
            $charityId = $this->getCharityId($request);
            
            // IMPORTANT: Always filter by charity for data privacy
            if (!$charityId) {
                return response()->json(['data' => []]);
            }
            
            $query = Campaign::query();
            if ($charityId) {
                $query->where('charity_id', $charityId);
            }
            
            $ranges = [
                ['label' => 'Under ₱10K', 'min' => 0, 'max' => 10000],
                ['label' => '₱10K-50K', 'min' => 10000, 'max' => 50000],
                ['label' => '₱50K-100K', 'min' => 50000, 'max' => 100000],
                ['label' => '₱100K-500K', 'min' => 100000, 'max' => 500000],
                ['label' => 'Over ₱500K', 'min' => 500000, 'max' => PHP_INT_MAX],
            ];
            
            $data = [];
            foreach ($ranges as $range) {
                $count = (clone $query)
                    ->where('target_amount', '>=', $range['min'])
                    ->where('target_amount', '<', $range['max'])
                    ->count();
                
                $data[] = [
                    'label' => $range['label'],
                    'value' => $count,
                ];
            }
            
            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            \Log::error('Fund ranges error: ' . $e->getMessage());
            return response()->json(['data' => []], 200);
        }
    }
    
    /**
     * GET /api/analytics/campaigns/top-performers
     * Top performing campaigns by raised amount (calculated from completed donations)
     */
    public function topPerformers(Request $request)
    {
        try {
            $charityId = $request->query('charity_id');
            $limit = $request->query('limit', 5);
            
            $query = Campaign::select(
                    'campaigns.id',
                    'campaigns.title',
                    'campaigns.campaign_type',
                    'campaigns.charity_id',
                    'campaigns.target_amount',
                    'campaigns.status',
                    DB::raw('COALESCE(SUM(CASE WHEN donations.status = "completed" THEN donations.amount ELSE 0 END), 0) as raised_amount'),
                    DB::raw('COALESCE(COUNT(CASE WHEN donations.status = "completed" THEN donations.id END), 0) as donation_count')
                )
                ->leftJoin('donations', 'campaigns.id', '=', 'donations.campaign_id')
                ->where('campaigns.status', '!=', 'archived')
                ->when($charityId, fn($q) => $q->where('campaigns.charity_id', $charityId))
                ->with('charity:id,name')
                ->groupBy(
                    'campaigns.id',
                    'campaigns.title',
                    'campaigns.campaign_type',
                    'campaigns.charity_id',
                    'campaigns.target_amount',
                    'campaigns.status'
                )
                ->orderByDesc('raised_amount')
                ->limit($limit)
                ->get()
                ->map(function($campaign) {
                    return [
                        'id' => $campaign->id,
                        'title' => $campaign->title,
                        'campaign_type' => $campaign->campaign_type,
                        'charity' => optional($campaign->charity)->name ?? 'Unknown',
                        'raised_amount' => (float) $campaign->raised_amount,
                        'goal_amount' => (float) $campaign->target_amount,
                        'progress' => $campaign->target_amount > 0 
                            ? round(($campaign->raised_amount / $campaign->target_amount) * 100, 1)
                            : 0,
                        'donation_count' => (int) $campaign->donation_count,
                        'status' => $campaign->status,
                    ];
                });
            
            \Log::info('Top performers query result:', ['count' => $query->count(), 'data' => $query->toArray()]);
            
            return response()->json([
                'data' => $query
            ]);
        } catch (\Exception $e) {
            \Log::error('Top performers analytics error: ' . $e->getMessage() . ' | Stack: ' . $e->getTraceAsString());
            return response()->json([
                'data' => []
            ], 200);
        }
    }
    
    /**
     * GET /api/analytics/insights
     * Comprehensive insights and narrative summary
     */
    public function getInsights(Request $request)
    {
        try {
            $charityId = $request->query('charity_id');
            $dateRange = now()->subDays(30);
            
            // Total donations and change
            $currentTotal = Donation::when($charityId, fn($q) => 
                $q->whereHas('campaign', fn($c) => $c->where('charity_id', $charityId))
            )
            ->where('created_at', '>=', $dateRange)
            ->sum('amount');
            
            $previousTotal = Donation::when($charityId, fn($q) => 
                $q->whereHas('campaign', fn($c) => $c->where('charity_id', $charityId))
            )
            ->whereBetween('created_at', [now()->subDays(60), $dateRange])
            ->sum('amount');
            
            $change = $previousTotal > 0 ? (($currentTotal - $previousTotal) / $previousTotal) * 100 : ($currentTotal > 0 ? 100 : 0);
            
            // Top campaign type by raised amount
            $topTypeQuery = Campaign::select('campaign_type', DB::raw('SUM(raised_amount) as total'))
                ->where('status', '!=', 'archived')
                ->when($charityId, fn($q) => $q->where('charity_id', $charityId))
                ->groupBy('campaign_type')
                ->orderByDesc('total')
                ->first();
            
            $topType = $topTypeQuery ? $topTypeQuery->campaign_type : null;
            $topTypeAmount = $topTypeQuery ? (float) $topTypeQuery->total : 0;
            
            // Top beneficiary group
            $topBeneficiaryQuery = Campaign::select('beneficiary_group', DB::raw('COUNT(*) as total'))
                ->where('status', '!=', 'archived')
                ->whereNotNull('beneficiary_group')
                ->where('beneficiary_group', '!=', '')
                ->when($charityId, fn($q) => $q->where('charity_id', $charityId))
                ->groupBy('beneficiary_group')
                ->orderByDesc('total')
                ->first();
            
            $topBeneficiary = $topBeneficiaryQuery ? $topBeneficiaryQuery->beneficiary_group : null;
            $topBeneficiaryCount = $topBeneficiaryQuery ? (int) $topBeneficiaryQuery->total : 0;
            
            // Top location
            $topLocationQuery = Campaign::select('city', DB::raw('COUNT(*) as total'))
                ->where('status', '!=', 'archived')
                ->whereNotNull('city')
                ->where('city', '!=', '')
                ->when($charityId, fn($q) => $q->where('charity_id', $charityId))
                ->groupBy('city')
                ->orderByDesc('total')
                ->first();
            
            $topLocation = $topLocationQuery ? $topLocationQuery->city : null;
            $topLocationCount = $topLocationQuery ? (int) $topLocationQuery->total : 0;
            
            // Campaign frequency
            $campaignsLast30Days = Campaign::when($charityId, fn($q) => $q->where('charity_id', $charityId))
                ->where('created_at', '>=', now()->subDays(30))
                ->count();
            $avgCampaignsPerWeek = round($campaignsLast30Days / 4, 1);
            
            // Donor engagement (repeat donors)
            $donorEngagement = Donation::when($charityId, function($q) use ($charityId) {
                    $q->whereHas('campaign', fn($c) => $c->where('charity_id', $charityId));
                })
                ->where('created_at', '>=', now()->subDays(60))
                ->whereNotNull('donor_id')
                ->select('donor_id', DB::raw('COUNT(*) as donation_count'))
                ->groupBy('donor_id')
                ->get();
            
            $totalDonors = $donorEngagement->count();
            $repeatDonors = $donorEngagement->where('donation_count', '>', 1)->count();
            $repeatDonorPercentage = $totalDonors > 0 ? round(($repeatDonors / $totalDonors) * 100, 1) : 0;
            
            return response()->json([
                'current_total' => (float) $currentTotal,
                'previous_total' => (float) $previousTotal,
                'change' => round($change, 2),
                'top_type' => $topType,
                'top_type_label' => $topType ? ucwords(str_replace('_', ' ', $topType)) : null,
                'top_type_amount' => $topTypeAmount,
                'top_beneficiary' => $topBeneficiary,
                'top_beneficiary_count' => $topBeneficiaryCount,
                'top_location' => $topLocation,
                'top_location_count' => $topLocationCount,
                'avg_campaigns_per_week' => $avgCampaignsPerWeek,
                'repeat_donor_percentage' => $repeatDonorPercentage,
                'total_donors' => $totalDonors,
                'repeat_donors' => $repeatDonors,
            ]);
        } catch (\Exception $e) {
            \Log::error('Insights error: ' . $e->getMessage());
            return response()->json([
                'current_total' => 0,
                'previous_total' => 0,
                'change' => 0,
                'top_type' => null,
                'top_type_label' => null,
                'top_type_amount' => 0,
                'top_beneficiary' => null,
                'top_beneficiary_count' => 0,
                'top_location' => null,
                'top_location_count' => 0,
                'avg_campaigns_per_week' => 0,
                'repeat_donor_percentage' => 0,
                'total_donors' => 0,
                'repeat_donors' => 0,
            ], 200);
        }
    }
}
