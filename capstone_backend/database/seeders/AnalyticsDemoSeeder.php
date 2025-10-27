<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Campaign, Donation, User, Charity};
use Illuminate\Support\Facades\Hash;

class AnalyticsDemoSeeder extends Seeder
{
    /**
     * Seed analytics demo data for presentation
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding Analytics Demo Data...');
        
        // Create demo donors
        $donors = $this->createDonors();
        $this->command->info('✓ Created ' . count($donors) . ' demo donors');
        
        // Get or create demo charities
        $charities = $this->getOrCreateCharities();
        $this->command->info('✓ Found/Created ' . count($charities) . ' demo charities');
        
        // Create diverse campaigns
        $campaigns = $this->createCampaigns($charities);
        $this->command->info('✓ Created ' . count($campaigns) . ' demo campaigns');
        
        // Create realistic donations
        $donations = $this->createDonations($campaigns, $donors);
        $this->command->info('✓ Created ' . count($donations) . ' demo donations');
        
        $this->command->info('🎉 Demo data seeded successfully!');
    }
    
    private function createDonors(): array
    {
        $donors = [];
        $names = [
            'Juan Dela Cruz', 'Maria Santos', 'Jose Reyes', 'Ana Garcia',
            'Carlos Mendoza', 'Sofia Aquino', 'Miguel Rodriguez', 'Isabella Cruz',
            'Luis Fernandez', 'Carmen Lopez'
        ];
        
        foreach ($names as $index => $name) {
            $email = 'donor' . ($index + 1) . '@demo.com';
            
            $donor = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'donor',
                ]
            );
            
            $donors[] = $donor;
        }
        
        return $donors;
    }
    
    private function getOrCreateCharities(): array
    {
        $charityData = [
            ['name' => 'Philippine Education Foundation', 'focus' => 'education', 'email' => 'pef@demo.com'],
            ['name' => 'Food for All PH', 'focus' => 'feeding_program', 'email' => 'foodforall@demo.com'],
            ['name' => 'Medical Aid Society', 'focus' => 'medical', 'email' => 'medaid@demo.com'],
            ['name' => 'Disaster Relief Network', 'focus' => 'disaster_relief', 'email' => 'drn@demo.com'],
            ['name' => 'Green Earth Alliance', 'focus' => 'environment', 'email' => 'greenearth@demo.com'],
        ];
        
        $charities = [];
        foreach ($charityData as $data) {
            $charity = Charity::where('name', $data['name'])->first();
            
            if (!$charity) {
                // Create charity user first
                $user = User::firstOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['name'] . ' Admin',
                        'password' => Hash::make('password'),
                        'role' => 'charity_admin',
                    ]
                );
                
                // Create charity
                $charity = Charity::create([
                    'user_id' => $user->id,
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'contact_number' => '09' . rand(100000000, 999999999),
                    'address' => 'Sample Address, Manila',
                    'mission' => 'Dedicated to ' . $data['focus'] . ' initiatives',
                    'vision' => 'A better future through ' . $data['focus'],
                    'about' => 'We are committed to making a difference in ' . $data['focus'] . '.',
                    'registration_number' => 'REG-' . strtoupper(substr($data['focus'], 0, 3)) . '-' . rand(1000, 9999),
                    'verification_status' => 'approved',
                    'date_registered' => now()->subYears(rand(1, 5)),
                ]);
            }
            
            $charities[] = $charity;
        }
        
        return $charities;
    }
    
    private function createCampaigns($charities): array
    {
        if (empty($charities)) {
            $this->command->warn('No charities found. Please ensure charities exist first.');
            return [];
        }
        
        $campaigns = [];
        $campaignTemplates = [
            // Education campaigns
            [
                'charity_index' => 0,
                'title' => 'School Supplies for 500 Students',
                'description' => 'Provide essential school supplies to underprivileged students in Metro Manila',
                'campaign_type' => 'education',
                'beneficiary' => '500 elementary students from low-income families in Tondo, Manila',
                'target_amount' => 150000,
                'region' => 'National Capital Region',
                'province' => 'Metro Manila',
                'city' => 'Manila',
            ],
            [
                'charity_index' => 0,
                'title' => 'Scholarship Fund 2025',
                'description' => 'Support deserving students to continue their education',
                'campaign_type' => 'education',
                'beneficiary' => '100 high school students in Quezon City',
                'target_amount' => 500000,
                'region' => 'National Capital Region',
                'province' => 'Metro Manila',
                'city' => 'Quezon City',
            ],
            
            // Feeding programs
            [
                'charity_index' => 1,
                'title' => 'Daily Meals for Children',
                'description' => 'Provide nutritious meals to malnourished children',
                'campaign_type' => 'feeding_program',
                'beneficiary' => '200 malnourished children aged 5-12 in Payatas',
                'target_amount' => 80000,
                'region' => 'National Capital Region',
                'province' => 'Metro Manila',
                'city' => 'Quezon City',
            ],
            [
                'charity_index' => 1,
                'title' => 'Weekend Food Packs',
                'description' => 'Weekend food assistance for families in need',
                'campaign_type' => 'feeding_program',
                'beneficiary' => '150 families in Pasig City',
                'target_amount' => 60000,
                'region' => 'National Capital Region',
                'province' => 'Metro Manila',
                'city' => 'Pasig',
            ],
            
            // Medical campaigns
            [
                'charity_index' => 2,
                'title' => 'Free Medical Mission',
                'description' => 'Provide free medical checkups and medicines',
                'campaign_type' => 'medical',
                'beneficiary' => '300 senior citizens in rural communities',
                'target_amount' => 200000,
                'region' => 'Central Luzon',
                'province' => 'Pampanga',
                'city' => 'Angeles City',
            ],
            [
                'charity_index' => 2,
                'title' => 'Cancer Patient Support',
                'description' => 'Financial assistance for cancer treatment',
                'campaign_type' => 'medical',
                'beneficiary' => '50 cancer patients undergoing chemotherapy',
                'target_amount' => 1000000,
                'region' => 'National Capital Region',
                'province' => 'Metro Manila',
                'city' => 'Makati',
            ],
            
            // Disaster relief
            [
                'charity_index' => 3,
                'title' => 'Typhoon Relief Packages',
                'description' => 'Emergency relief for typhoon victims',
                'campaign_type' => 'disaster_relief',
                'beneficiary' => '500 families affected by recent typhoon',
                'target_amount' => 300000,
                'region' => 'Eastern Visayas',
                'province' => 'Leyte',
                'city' => 'Tacloban City',
            ],
            
            // Environment
            [
                'charity_index' => 4,
                'title' => 'Tree Planting Initiative',
                'description' => 'Plant 10,000 trees to combat climate change',
                'campaign_type' => 'environment',
                'beneficiary' => 'Communities in 5 municipalities',
                'target_amount' => 120000,
                'region' => 'Calabarzon',
                'province' => 'Laguna',
                'city' => 'Los Baños',
            ],
        ];
        
        foreach ($campaignTemplates as $template) {
            if (!isset($charities[$template['charity_index']])) continue;
            
            $campaign = Campaign::create([
                'charity_id' => $charities[$template['charity_index']]->id,
                'title' => $template['title'],
                'description' => $template['description'],
                'problem' => 'Many ' . $template['beneficiary'] . ' face significant challenges.',
                'solution' => 'Through this campaign, we will provide direct support and resources.',
                'expected_outcome' => 'Improved quality of life and opportunities for beneficiaries.',
                'campaign_type' => $template['campaign_type'],
                'beneficiary' => $template['beneficiary'],
                'target_amount' => $template['target_amount'],
                'region' => $template['region'],
                'province' => $template['province'],
                'city' => $template['city'],
                'donation_type' => 'one_time',
                'status' => 'published',
                'start_date' => now()->subDays(rand(30, 90)),
                'end_date' => now()->addDays(rand(30, 180)),
            ]);
            
            $campaigns[] = $campaign;
        }
        
        return $campaigns;
    }
    
    private function createDonations($campaigns, $donors): array
    {
        if (empty($campaigns) || empty($donors)) {
            return [];
        }
        
        $donations = [];
        $donationAmounts = [500, 1000, 2000, 3000, 5000, 10000, 15000, 20000];
        
        // Create donations over the past 60 days
        foreach ($campaigns as $campaign) {
            // Random number of donations per campaign (5-20)
            $donationCount = rand(5, 20);
            
            for ($i = 0; $i < $donationCount; $i++) {
                $donor = $donors[array_rand($donors)];
                $amount = $donationAmounts[array_rand($donationAmounts)];
                
                // 80% completed, 15% pending, 5% rejected
                $statusRoll = rand(1, 100);
                if ($statusRoll <= 80) {
                    $status = 'completed';
                } elseif ($statusRoll <= 95) {
                    $status = 'pending';
                } else {
                    $status = 'rejected';
                }
                
                // Create donation in the past 60 days
                $daysAgo = rand(1, 60);
                
                $donation = Donation::create([
                    'donor_id' => $donor->id,
                    'donor_name' => $donor->name,
                    'donor_email' => $donor->email,
                    'charity_id' => $campaign->charity_id,
                    'campaign_id' => $campaign->id,
                    'amount' => $amount,
                    'status' => $status,
                    'purpose' => 'project',
                    'is_anonymous' => rand(1, 10) > 8, // 20% anonymous
                    'donated_at' => now()->subDays($daysAgo)->subHours(rand(0, 23)),
                    'created_at' => now()->subDays($daysAgo),
                ]);
                
                $donations[] = $donation;
            }
        }
        
        return $donations;
    }
}
