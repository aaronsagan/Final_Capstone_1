<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{User, Charity, DonationChannel, Campaign};

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create System Admin
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'status' => 'active',
                'phone' => '09171111111'
            ]
        );

        // Create Demo Donor
        User::updateOrCreate(
            ['email' => 'donor@example.com'],
            [
                'name' => 'Demo Donor',
                'password' => bcrypt('password'),
                'role' => 'donor',
                'status' => 'active',
                'phone' => '09172222222',
                'address' => '123 Donor Street, Manila'
            ]
        );

        // Create Charity Admin
        $owner = User::updateOrCreate(
            ['email' => 'charityadmin@example.com'],
            [
                'name' => 'Charity Admin',
                'password' => bcrypt('password'),
                'role' => 'charity_admin',
                'status' => 'active',
                'phone' => '09173333333'
            ]
        );

        // Verified charity so it appears in public lists
        $charity = Charity::firstOrCreate(
            ['name' => 'HopeWorks Foundation'],
            [
                'owner_id' => $owner->id,
                'mission' => 'Transparent community support.',
                'vision' => 'A better future for all communities.',
                'contact_email' => 'contact@hopeworks.org',
                'contact_phone' => '09173333333',
                'address' => '456 Charity Ave, Quezon City',
                'region' => 'Metro Manila',
                'municipality' => 'Quezon City',
                'category' => 'Community Development',
                'verification_status' => 'approved',
            ]
        );

        // Display-only donation channel (e.g., GCash)
        DonationChannel::firstOrCreate(
            ['charity_id' => $charity->id, 'label' => 'GCash Main'],
            ['type' => 'gcash', 'details' => ['number' => '09171234567', 'account_name' => 'HopeWorks'], 'is_active' => true]
        );

        // One published campaign
        Campaign::firstOrCreate(
            ['charity_id' => $charity->id, 'title' => 'School Kits 2025'],
            ['description' => 'Provide school kits to 500 students.',
             'target_amount' => 250000, 'status' => 'published']
        );
    }
}
