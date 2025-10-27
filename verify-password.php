<?php

require __DIR__ . '/capstone_backend/vendor/autoload.php';

$app = require_once __DIR__ . '/capstone_backend/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Password Verification Test ===\n\n";

$email = 'admin@example.com';
$password = 'password';

$user = \App\Models\User::where('email', $email)->first();

if (!$user) {
    echo "❌ User not found: $email\n";
    exit(1);
}

echo "✓ User found:\n";
echo "  Email: {$user->email}\n";
echo "  Name: {$user->name}\n";
echo "  Role: {$user->role}\n";
echo "  Status: {$user->status}\n";
echo "  Password Hash: " . substr($user->password, 0, 20) . "...\n\n";

// Test password
$passwordCheck = \Illuminate\Support\Facades\Hash::check($password, $user->password);

echo "Testing password: '$password'\n";
echo "Result: " . ($passwordCheck ? "✓ MATCH" : "❌ NO MATCH") . "\n\n";

// Test all conditions from login method
echo "=== Login Conditions Check ===\n";
echo "1. User exists: " . ($user ? "✓ YES" : "❌ NO") . "\n";
echo "2. Password matches: " . ($passwordCheck ? "✓ YES" : "❌ NO") . "\n";
echo "3. Status is active: " . ($user->status === 'active' ? "✓ YES" : "❌ NO (status: {$user->status})") . "\n\n";

if ($user && $passwordCheck && $user->status === 'active') {
    echo "✓✓✓ All conditions met - Login should work!\n";
} else {
    echo "❌ Login will fail - check conditions above\n";
}

// Test creating a new hash
echo "\n=== Hash Test ===\n";
$newHash = \Illuminate\Support\Facades\Hash::make($password);
echo "New hash for '$password': " . substr($newHash, 0, 30) . "...\n";
$newCheck = \Illuminate\Support\Facades\Hash::check($password, $newHash);
echo "New hash verification: " . ($newCheck ? "✓ WORKS" : "❌ FAILS") . "\n";
