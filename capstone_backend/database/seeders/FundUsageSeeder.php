<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FundUsageLog;
use App\Models\Campaign;

class FundUsageSeeder extends Seeder
{
    /**
     * Seed fund usage logs for campaign ID 3 (Backpacks of Hope 2025)
     */
    public function run(): void
    {
        $campaignId = 3; // Backpacks of Hope 2025
        $campaign = Campaign::find($campaignId);
        
        if (!$campaign) {
            $this->command->warn("Campaign ID {$campaignId} not found. Skipping fund usage seeder.");
            return;
        }

        $fundUsages = [
            [
                'campaign_id' => $campaignId,
                'charity_id' => $campaign->charity_id,
                'amount' => 15000.00,
                'category' => 'supplies',
                'description' => 'Purchased 200 school backpacks from approved supplier. Each backpack includes notebooks, pens, pencils, erasers, and rulers.',
                'spent_at' => now()->subDays(5),
                'attachment_path' => null,
            ],
            [
                'campaign_id' => $campaignId,
                'charity_id' => $campaign->charity_id,
                'amount' => 8500.00,
                'category' => 'supplies',
                'description' => 'Bought nutritious snack packages (crackers, biscuits, juice boxes) for 200 children. Enough for 2 weeks supply.',
                'spent_at' => now()->subDays(4),
                'attachment_path' => null,
            ],
            [
                'campaign_id' => $campaignId,
                'charity_id' => $campaign->charity_id,
                'amount' => 3200.00,
                'category' => 'supplies',
                'description' => 'Hygiene kits purchased including soap, toothbrush, toothpaste, shampoo, and hand sanitizer for each child.',
                'spent_at' => now()->subDays(3),
                'attachment_path' => null,
            ],
            [
                'campaign_id' => $campaignId,
                'charity_id' => $campaign->charity_id,
                'amount' => 2500.00,
                'category' => 'transport',
                'description' => 'Transportation costs for delivering backpacks to 5 different barangay schools in low-income communities.',
                'spent_at' => now()->subDays(2),
                'attachment_path' => null,
            ],
            [
                'campaign_id' => $campaignId,
                'charity_id' => $campaign->charity_id,
                'amount' => 1800.00,
                'category' => 'operations',
                'description' => 'Printing and packaging materials for backpack assembly. Includes labels, tags, and protective wrapping.',
                'spent_at' => now()->subDays(1),
                'attachment_path' => null,
            ],
            [
                'campaign_id' => $campaignId,
                'charity_id' => $campaign->charity_id,
                'amount' => 4500.00,
                'category' => 'staffing',
                'description' => 'Volunteer coordination and logistics support. Meals and refreshments for 25 volunteers during packing days.',
                'spent_at' => now()->subDays(6),
                'attachment_path' => null,
            ],
        ];

        foreach ($fundUsages as $usage) {
            FundUsageLog::create($usage);
        }

        $this->command->info('✅ Created ' . count($fundUsages) . ' fund usage records for Campaign ID ' . $campaignId);
    }
}
