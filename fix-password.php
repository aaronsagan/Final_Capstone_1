<?php

require_once 'capstone_backend/vendor/autoload.php';

// Load Laravel environment
$app = require_once 'capstone_backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "Fixing password for iflicdmi.staf@gmail.com...\n";

$user = User::where('email', 'iflicdmi.staf@gmail.com')->first();

if (!$user) {
    echo "❌ User not found!\n";
    exit(1);
}

echo "Found user: {$user->name} ({$user->email})\n";
echo "Current role: {$user->role}\n";

// Update password with proper bcrypt hashing
$user->password = Hash::make('password');
$user->save();

echo "✅ Password updated successfully!\n";
echo "New password: password\n";

// Verify the hash works
if (Hash::check('password', $user->password)) {
    echo "✅ Password verification successful - you can now login!\n";
} else {
    echo "❌ Password verification failed\n";
}

echo "\nLogin credentials:\n";
echo "Email: iflicdmi.staf@gmail.com\n";
echo "Password: password\n";
