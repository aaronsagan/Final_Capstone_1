<?php

namespace App\Http\Controllers;

use App\Models\{Charity, Campaign, Donation, FundUsageLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransparencyController extends Controller
{
    // Get comprehensive transparency data for a charity
    public function charityTransparency(Request $request, Charity $charity)
    {
        // Verify access
        if ($request->user() && $request->user()->role === 'charity_admin') {
            abort_unless($charity->owner_id === $request->user()->id, 403);
        }

        $period = $request->get('period', 'all'); // all, month, quarter, year

        $startDate = $this->getStartDate($period);

        // Get donation summary
        $donations = $charity->donations()
            ->when($startDate, fn($q) => $q->where('donated_at', '>=', $startDate))
            ->selectRaw('
                SUM(amount) as total_amount,
                COUNT(*) as total_donations,
                AVG(amount) as avg_donation,
                SUM(CASE WHEN purpose = "general" THEN amount ELSE 0 END) as general_funds,
                SUM(CASE WHEN purpose = "project" THEN amount ELSE 0 END) as project_funds,
                SUM(CASE WHEN purpose = "emergency" THEN amount ELSE 0 END) as emergency_funds
            ')
            ->first();

        // Get fund usage summary
        $fundUsage = $charity->fundUsageLogs()
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->selectRaw('
                SUM(amount) as total_spent,
                COUNT(*) as total_expenses,
                AVG(amount) as avg_expense
            ')
            ->first();

        // Get campaign progress
        $campaigns = $charity->campaigns()
            ->when($startDate, function($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            })
            ->with(['donations' => function($q) use ($startDate) {
                $q->when($startDate, fn($q) => $q->where('donated_at', '>=', $startDate));
            }])
            ->get()
            ->map(function($campaign) {
                $raised = $campaign->donations->sum('amount');
                $progress = $campaign->target_amount > 0 ? ($raised / $campaign->target_amount) * 100 : 0;

                return [
                    'id' => $campaign->id,
                    'title' => $campaign->title,
                    'target_amount' => $campaign->target_amount,
                    'raised_amount' => $raised,
                    'progress_percentage' => min($progress, 100),
                    'status' => $campaign->status,
                    'created_at' => $campaign->created_at,
                ];
            });

        // Get fund allocation breakdown
        $fundAllocation = $charity->fundUsageLogs()
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->selectRaw('category, SUM(amount) as total_amount')
            ->groupBy('category')
            ->orderBy('total_amount', 'desc')
            ->get()
            ->map(function($item) use ($fundUsage) {
                return [
                    'category' => $item->category,
                    'amount' => $item->total_amount,
                    'percentage' => $fundUsage->total_spent > 0 ?
                        ($item->total_amount / $fundUsage->total_spent) * 100 : 0
                ];
            });

        return response()->json([
            'charity' => [
                'id' => $charity->id,
                'name' => $charity->name,
                'verification_status' => $charity->verification_status,
            ],
            'period' => $period,
            'summary' => [
                'total_received' => $donations->total_amount ?? 0,
                'total_donations' => $donations->total_donations ?? 0,
                'avg_donation' => $donations->avg_donation ?? 0,
                'total_spent' => $fundUsage->total_spent ?? 0,
                'net_funds' => ($donations->total_amount ?? 0) - ($fundUsage->total_spent ?? 0),
            ],
            'fund_sources' => [
                'general' => $donations->general_funds ?? 0,
                'project' => $donations->project_funds ?? 0,
                'emergency' => $donations->emergency_funds ?? 0,
            ],
            'campaigns' => $campaigns,
            'fund_allocation' => $fundAllocation,
            'recent_transactions' => $this->getRecentTransactions($charity, $startDate),
        ]);
    }

    // Get public transparency data for donors
    public function publicTransparency(Request $request, Charity $charity)
    {
        // Only show data for approved charities
        abort_unless($charity->verification_status === 'approved', 403);

        return $this->charityTransparency($request, $charity);
    }

    // Get donor's personal transparency view
    public function donorTransparency(Request $request)
    {
        $donor = $request->user();
        abort_unless($donor->role === 'donor', 403);

        $period = $request->get('period', 'all');
        $startDate = $this->getStartDate($period);

        // Get donor's donation summary
        $donations = $donor->donations()
            ->when($startDate, fn($q) => $q->where('donated_at', '>=', $startDate))
            ->with(['charity', 'campaign'])
            ->selectRaw('
                SUM(amount) as total_donated,
                COUNT(*) as total_donations,
                AVG(amount) as avg_donation,
                COUNT(DISTINCT charity_id) as charities_supported
            ')
            ->first();

        // Get donations by purpose
        $donationsByPurpose = $donor->donations()
            ->when($startDate, fn($q) => $q->where('donated_at', '>=', $startDate))
            ->selectRaw('purpose, SUM(amount) as total_amount, COUNT(*) as count')
            ->groupBy('purpose')
            ->get()
            ->map(function($item) use ($donations) {
                return [
                    'purpose' => $item->purpose,
                    'amount' => $item->total_amount,
                    'count' => $item->count,
                    'percentage' => $donations->total_donated > 0 ?
                        ($item->total_amount / $donations->total_donated) * 100 : 0
                ];
            });

        // Get recent donations with charity feedback
        $recentDonations = $donor->donations()
            ->when($startDate, fn($q) => $q->where('donated_at', '>=', $startDate))
            ->with(['charity', 'campaign'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function($donation) {
                return [
                    'id' => $donation->id,
                    'amount' => $donation->amount,
                    'purpose' => $donation->purpose,
                    'status' => $donation->status,
                    'receipt_no' => $donation->receipt_no,
                    'charity_name' => $donation->charity->name,
                    'campaign_title' => $donation->campaign?->title,
                    'donated_at' => $donation->donated_at,
                ];
            });

        return response()->json([
            'period' => $period,
            'summary' => [
                'total_donated' => $donations->total_donated ?? 0,
                'total_donations' => $donations->total_donations ?? 0,
                'avg_donation' => $donations->avg_donation ?? 0,
                'charities_supported' => $donations->charities_supported ?? 0,
            ],
            'donations_by_purpose' => $donationsByPurpose,
            'recent_donations' => $recentDonations,
        ]);
    }

    private function getStartDate($period)
    {
        switch($period) {
            case 'month':
                return now()->startOfMonth();
            case 'quarter':
                return now()->startOfQuarter();
            case 'year':
                return now()->startOfYear();
            default:
                return null;
        }
    }

    private function getRecentTransactions($charity, $startDate)
    {
        // Get recent donations
        $recentDonations = $charity->donations()
            ->when($startDate, fn($q) => $q->where('donated_at', '>=', $startDate))
            ->with(['donor', 'campaign'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function($donation) {
                return [
                    'type' => 'donation',
                    'id' => $donation->id,
                    'amount' => $donation->amount,
                    'purpose' => $donation->purpose,
                    'status' => $donation->status,
                    'is_anonymous' => $donation->is_anonymous,
                    'donor_name' => $donation->is_anonymous ? 'Anonymous' : $donation->donor?->name,
                    'campaign_title' => $donation->campaign?->title,
                    'date' => $donation->donated_at,
                ];
            });

        // Get recent fund usage
        $recentExpenses = $charity->fundUsageLogs()
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->latest()
            ->take(5)
            ->get()
            ->map(function($usage) {
                return [
                    'type' => 'expense',
                    'id' => $usage->id,
                    'amount' => $usage->amount,
                    'category' => $usage->category,
                    'description' => $usage->description,
                    'date' => $usage->created_at,
                ];
            });

        // Combine and sort by date
        $transactions = $recentDonations->concat($recentExpenses)->sortByDesc('date')->take(10);

        return $transactions->values()->all();
    }
}
