<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Charity;
use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CleanDatabase extends Command
{
    protected $signature = 'db:clean';
    protected $description = 'Clean all seeded data and create fresh admin account';

    public function handle()
    {
        $this->info('Cleaning database...');

        // Delete all data except admin
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clean all tables
        DB::table('donations')->truncate();
        DB::table('campaigns')->truncate();
        DB::table('charity_posts')->truncate();
        DB::table('charities')->truncate();
        
        // Delete all users except we'll create a fresh admin
        DB::table('users')->truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create only system admin
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@charityhub.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'active',
            'phone' => '09171234567'
        ]);

        $this->info('✓ Database cleaned successfully');
        $this->info('✓ Created fresh admin account:');
        $this->info('  Email: admin@charityhub.com');
        $this->info('  Password: admin123');
        $this->info('');
        $this->info('Database is now clean and ready for real data!');

        return 0;
    }
}
