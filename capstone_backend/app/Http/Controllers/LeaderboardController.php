<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\User;
use App\Models\Charity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    /**
     * Get top donors globally (Public)
     */
    public function topDonors(Request $request)
    {
        $limit = min($request->get('limit', 10), 50); // Max 50 results

        $topDonors = User::select('users.id', 'users.name', 'users.profile_image')
            ->join('donations', 'users.id', '=', 'donations.donor_id')
            ->where('donations.status', 'completed')
            ->where('donations.is_anonymous', false) // Respect anonymity
            ->where('users.role', 'donor')
            ->groupBy('users.id', 'users.name', 'users.profile_image')
            ->selectRaw('SUM(donations.amount) as total_donated, COUNT(donations.id) as donation_count')
            ->orderBy('total_donated', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'top_donors' => $topDonors,
            'total_count' => $topDonors->count(),
        ]);
    }

    /**
     * Get top donors for a specific charity
     */
    public function topDonorsForCharity(Request $request, Charity $charity)
    {
        $limit = min($request->get('limit', 10), 50);

        $topDonors = User::select('users.id', 'users.name', 'users.profile_image')
            ->join('donations', 'users.id', '=', 'donations.donor_id')
            ->where('donations.charity_id', $charity->id)
            ->where('donations.status', 'completed')
            ->where('donations.is_anonymous', false)
            ->where('users.role', 'donor')
            ->groupBy('users.id', 'users.name', 'users.profile_image')
            ->selectRaw('SUM(donations.amount) as total_donated, COUNT(donations.id) as donation_count')
            ->orderBy('total_donated', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'charity' => $charity->only(['id', 'name', 'logo_path']),
            'top_donors' => $topDonors,
            'total_count' => $topDonors->count(),
        ]);
    }

    /**
     * Get top performing charities by total donations received
     */
    public function topCharities(Request $request)
    {
        $limit = min($request->get('limit', 10), 50);

        $topCharities = Charity::select('charities.id', 'charities.name', 'charities.logo_path', 'charities.category')
            ->join('donations', 'charities.id', '=', 'donations.charity_id')
            ->where('donations.status', 'completed')
            ->where('charities.verification_status', 'approved')
            ->groupBy('charities.id', 'charities.name', 'charities.logo_path', 'charities.category')
            ->selectRaw('SUM(donations.amount) as total_received, COUNT(donations.id) as donation_count')
            ->orderBy('total_received', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'top_charities' => $topCharities,
            'total_count' => $topCharities->count(),
        ]);
    }

    /**
     * Get donation statistics and trends
     */
    public function donationStats()
    {
        $totalDonations = Donation::where('status', 'completed')->sum('amount');
        $totalDonationCount = Donation::where('status', 'completed')->count();
        $totalDonors = User::where('role', 'donor')->whereHas('donations', function($q) {
            $q->where('status', 'completed');
        })->count();

        // Monthly donation trends (last 12 months)
        $monthlyTrends = Donation::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as donation_count')
            )
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Top donation amounts (for recognition)
        $topDonations = Donation::select('amount', 'created_at')
            ->with(['donor:id,name', 'charity:id,name'])
            ->where('status', 'completed')
            ->where('is_anonymous', false)
            ->orderBy('amount', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'summary' => [
                'total_donations' => $totalDonations,
                'total_donation_count' => $totalDonationCount,
                'total_donors' => $totalDonors,
                'average_donation' => $totalDonationCount > 0 ? round($totalDonations / $totalDonationCount, 2) : 0,
            ],
            'monthly_trends' => $monthlyTrends,
            'top_donations' => $topDonations,
        ]);
    }

    /**
     * Get leaderboard for a specific time period
     */
    public function leaderboardByPeriod(Request $request)
    {
        $period = $request->get('period', 'all_time'); // all_time, this_year, this_month, this_week
        $type = $request->get('type', 'donors'); // donors, charities
        $limit = min($request->get('limit', 10), 50);

        $query = null;
        $dateFilter = null;

        switch ($period) {
            case 'this_week':
                $dateFilter = ['>=', now()->startOfWeek()];
                break;
            case 'this_month':
                $dateFilter = ['>=', now()->startOfMonth()];
                break;
            case 'this_year':
                $dateFilter = ['>=', now()->startOfYear()];
                break;
            default:
                $dateFilter = null;
        }

        if ($type === 'donors') {
            $query = User::select('users.id', 'users.name', 'users.profile_image')
                ->join('donations', 'users.id', '=', 'donations.donor_id')
                ->where('donations.status', 'completed')
                ->where('donations.is_anonymous', false)
                ->where('users.role', 'donor');

            if ($dateFilter) {
                $query->where('donations.created_at', $dateFilter[0], $dateFilter[1]);
            }

            $results = $query->groupBy('users.id', 'users.name', 'users.profile_image')
                ->selectRaw('SUM(donations.amount) as total_donated, COUNT(donations.id) as donation_count')
                ->orderBy('total_donated', 'desc')
                ->limit($limit)
                ->get();
        } else {
            $query = Charity::select('charities.id', 'charities.name', 'charities.logo_path')
                ->join('donations', 'charities.id', '=', 'donations.charity_id')
                ->where('donations.status', 'completed')
                ->where('charities.verification_status', 'approved');

            if ($dateFilter) {
                $query->where('donations.created_at', $dateFilter[0], $dateFilter[1]);
            }

            $results = $query->groupBy('charities.id', 'charities.name', 'charities.logo_path')
                ->selectRaw('SUM(donations.amount) as total_received, COUNT(donations.id) as donation_count')
                ->orderBy('total_received', 'desc')
                ->limit($limit)
                ->get();
        }

        return response()->json([
            'period' => $period,
            'type' => $type,
            'results' => $results,
            'total_count' => $results->count(),
        ]);
    }
}
