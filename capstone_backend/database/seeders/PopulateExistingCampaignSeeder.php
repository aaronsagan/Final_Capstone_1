<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campaign;
use App\Models\DonationChannel;

class PopulateExistingCampaignSeeder extends Seeder
{
    public function run(): void
    {
        // Find the "Backpacks of Hope 2025" campaign
        $campaign = Campaign::where('title', 'like', '%Backpacks%')->first();
        
        if (!$campaign) {
            $this->command->error('Campaign not found!');
            return;
        }

        $this->command->info("Found campaign: {$campaign->title} (ID: {$campaign->id})");

        // Update story fields
        $campaign->update([
            'problem' => 'Many children from low-income families lack essential school supplies, nutritious snacks, and hygiene kits. This prevents them from attending school with confidence and dignity, affecting their education and well-being.',
            'solution' => 'We are providing complete backpacks filled with school supplies, nutritious snacks, and hygiene kits to underprivileged children. Each backpack contains notebooks, pens, pencils, erasers, healthy snacks, soap, toothbrush, and other essentials.',
            'expected_outcome' => 'By providing these backpacks, we aim to empower every child to start the school year with confidence, dignity, and hope. We expect to reach 500 children across low-income communities before the new school year begins.',
        ]);

        $this->command->info('✓ Updated campaign story fields');

        // Get charity's active donation channels
        $charity = $campaign->charity;
        if (!$charity) {
            $this->command->error('Charity not found for this campaign!');
            return;
        }

        $channels = $charity->channels()
            ->where('is_active', true)
            ->get();

        if ($channels->isEmpty()) {
            $this->command->warn('⚠ No active donation channels found for this charity');
            $this->command->info('Please create donation channels first via the charity dashboard');
        } else {
            // Attach all active channels to the campaign
            $campaign->donationChannels()->sync($channels->pluck('id'));
            $this->command->info("✓ Attached {$channels->count()} donation channels to campaign");
        }

        $this->command->info("\n✅ Campaign updated successfully!");
        $this->command->info("Campaign ID: {$campaign->id}");
        $this->command->info("Channels attached: {$campaign->donationChannels()->count()}");
    }
}
