<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Admin - password: 'password'
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active'
            ]
        );

        // Donor - password: 'password'
        User::updateOrCreate(
            ['email' => 'donor@example.com'],
            [
                'name' => 'Test Donor',
                'password' => Hash::make('password'),
                'role' => 'donor',
                'status' => 'active'
            ]
        );

        // Charity Admin - password: 'password'
        User::updateOrCreate(
            ['email' => 'charity@example.com'],
            [
                'name' => 'Test Charity Admin',
                'password' => Hash::make('password'),
                'role' => 'charity_admin',
                'status' => 'active'
            ]
        );
    }
}
