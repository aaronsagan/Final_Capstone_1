<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campaign;
use App\Models\CampaignUpdate;

class CampaignUpdateSeeder extends Seeder
{
    public function run(): void
    {
        // Find the "Backpacks of Hope 2025" campaign
        $campaign = Campaign::where('title', 'like', '%Backpacks%')->first();
        
        if (!$campaign) {
            $this->command->error('Campaign not found!');
            return;
        }

        $this->command->info("Adding updates to campaign: {$campaign->title} (ID: {$campaign->id})");

        // Create sample updates
        $updates = [
            [
                'title' => '🎉 Campaign Launch - Let\'s Make a Difference!',
                'content' => "We're excited to launch the Backpacks of Hope 2025 campaign! Our goal is to provide 500 children with complete backpacks filled with school supplies, nutritious snacks, and hygiene kits.\n\nEach backpack costs just ₱1,000 and will help a child start the school year with confidence and dignity. Together, we can make education accessible for all!",
                'is_milestone' => false,
            ],
            [
                'title' => '🏁 Milestone: 25% Funding Reached!',
                'content' => "Thank you to all our amazing donors! We've reached 25% of our funding goal in just the first week. Your generosity is bringing us closer to our target of 500 backpacks.\n\n125 children will soon receive the supplies they need to succeed in school. Let's keep the momentum going!",
                'is_milestone' => true,
            ],
            [
                'title' => '📦 First Batch of Supplies Purchased',
                'content' => "Great news! We've purchased the first batch of school supplies including:\n- 200 notebooks\n- 300 pens and pencils\n- 150 erasers and sharpeners\n- 100 geometry sets\n\nOur volunteer team will begin assembling the backpacks next week. Stay tuned for photos!",
                'is_milestone' => false,
            ],
            [
                'title' => '🎒 First 200 Backpacks Distributed!',
                'content' => "What an incredible day! We successfully distributed 200 backpacks to children from 5 different communities today.\n\nThe joy on their faces was priceless. Each child received:\n✓ Complete school supplies\n✓ Hygiene kit (soap, toothbrush, etc.)\n✓ Nutritious snacks for the week\n✓ A message of hope and encouragement\n\nThank you to everyone who made this possible!",
                'is_milestone' => true,
            ],
            [
                'title' => '👥 Volunteer Team Expands',
                'content' => "Our volunteer team has grown to 25 dedicated members! We now have:\n- 10 packers assembling backpacks\n- 8 logistics coordinators\n- 5 community liaisons\n- 2 documentation specialists\n\nWould you like to join us? Visit our Volunteer page to sign up!",
                'is_milestone' => false,
            ],
        ];

        foreach ($updates as $index => $updateData) {
            CampaignUpdate::create([
                'campaign_id' => $campaign->id,
                'title' => $updateData['title'],
                'content' => $updateData['content'],
                'is_milestone' => $updateData['is_milestone'],
                'created_at' => now()->subDays(20 - ($index * 4)), // Spread updates over time
            ]);
        }

        $this->command->info("\n✅ Successfully created " . count($updates) . " campaign updates!");
        
        $milestoneCount = CampaignUpdate::where('campaign_id', $campaign->id)
            ->where('is_milestone', true)
            ->count();
        
        $this->command->info("📊 Total Updates: " . count($updates));
        $this->command->info("🏁 Milestones: " . $milestoneCount);
    }
}
