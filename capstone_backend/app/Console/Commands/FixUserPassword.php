<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class FixUserPassword extends Command
{
    protected $signature = 'user:fix-password {email} {password}';
    protected $description = 'Fix user password with proper bcrypt hashing';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }

        // Update password with proper bcrypt hashing
        $user->password = Hash::make($password);
        $user->save();

        $this->info("Password updated successfully for {$email}");
        $this->info("New password: {$password}");
        
        // Verify the hash works
        if (Hash::check($password, $user->password)) {
            $this->info("✓ Password hash verification successful");
        } else {
            $this->error("✗ Password hash verification failed");
        }

        return 0;
    }
}
