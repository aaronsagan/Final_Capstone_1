<?php

require __DIR__ . '/capstone_backend/vendor/autoload.php';

$app = require_once __DIR__ . '/capstone_backend/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Demo Accounts in Database ===\n\n";

$users = \App\Models\User::all(['id', 'email', 'name', 'role', 'status']);

if ($users->isEmpty()) {
    echo "❌ No users found in database!\n";
} else {
    echo "✓ Found " . $users->count() . " users:\n\n";
    foreach ($users as $user) {
        echo "Email: {$user->email}\n";
        echo "Name:  {$user->name}\n";
        echo "Role:  {$user->role}\n";
        echo "Status: {$user->status}\n";
        echo "---\n";
    }
    
    echo "\n=== Login Credentials ===\n\n";
    echo "All accounts use password: password\n\n";
    echo "Admin:   admin@example.com\n";
    echo "Donor:   donor@example.com\n";
    echo "Charity: charityadmin@example.com\n";
}
