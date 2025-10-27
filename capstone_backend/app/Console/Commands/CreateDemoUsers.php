<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Charity;
use Illuminate\Support\Facades\Hash;

class CreateDemoUsers extends Command
{
    protected $signature = 'demo:users';
    protected $description = 'Create demo users for testing';

    public function handle()
    {
        $this->info('Creating demo users...');

        // Fix existing user with bcrypt issue
        $existingUser = User::where('email', 'iflicdmi.staf@gmail.com')->first();
        if ($existingUser) {
            $existingUser->password = Hash::make('password');
            $existingUser->save();
            $this->info('✓ Fixed password for: iflicdmi.staf@gmail.com (password: password)');
        }

        // Create System Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'phone' => '09171111111'
            ]
        );
        $this->info('✓ Created: admin@example.com (password: password)');

        // Create Demo Donor
        $donor = User::updateOrCreate(
            ['email' => 'donor@example.com'],
            [
                'name' => 'Demo Donor',
                'password' => Hash::make('password'),
                'role' => 'donor',
                'status' => 'active',
                'phone' => '09172222222',
                'address' => '123 Donor Street, Manila'
            ]
        );
        $this->info('✓ Created: donor@example.com (password: password)');

        // Create Charity Admin
        $charityAdmin = User::updateOrCreate(
            ['email' => 'charityadmin@example.com'],
            [
                'name' => 'Charity Admin',
                'password' => Hash::make('password'),
                'role' => 'charity_admin',
                'status' => 'active',
                'phone' => '09173333333'
            ]
        );
        $this->info('✓ Created: charityadmin@example.com (password: password)');

        // Create charity for charity admin
        $charity = Charity::updateOrCreate(
            ['owner_id' => $charityAdmin->id],
            [
                'name' => 'HopeWorks Foundation',
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
        $this->info('✓ Created charity: HopeWorks Foundation');

        $this->info('');
        $this->info('Demo users created successfully!');
        $this->info('');
        $this->info('Login credentials:');
        $this->info('  Admin:   admin@example.com / password');
        $this->info('  Donor:   donor@example.com / password');
        $this->info('  Charity: charityadmin@example.com / password');

        return 0;
    }
}
