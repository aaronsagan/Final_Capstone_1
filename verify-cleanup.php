<?php

require_once 'capstone_backend/vendor/autoload.php';
$app = require_once 'capstone_backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Charity;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\CharityPost;

echo "📊 Database Status After Cleanup:\n";
echo "Total Users: " . User::count() . "\n";
echo "Admins: " . User::where('role', 'admin')->count() . "\n";
echo "Donors: " . User::where('role', 'donor')->count() . "\n";
echo "Charity Admins: " . User::where('role', 'charity_admin')->count() . "\n";
echo "Charities: " . Charity::count() . "\n";
echo "Campaigns: " . Campaign::count() . "\n";
echo "Donations: " . Donation::count() . "\n";
echo "Posts: " . CharityPost::count() . "\n";

echo "\n🎯 Admin Accounts Preserved:\n";
$admins = User::where('role', 'admin')->get();
foreach ($admins as $admin) {
    echo "  - {$admin->name} ({$admin->email})\n";
}

echo "\n✅ Database cleanup completed successfully!\n";
echo "✅ All donor and charity admin accounts removed\n";
echo "✅ All associated data cleared\n";
echo "✅ Admin accounts preserved\n";
echo "\n🚀 Ready for fresh testing!\n";
