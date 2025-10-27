<?php

require_once 'capstone_backend/vendor/autoload.php';

// Load Laravel environment
$app = require_once 'capstone_backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Charity;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\CharityPost;
use App\Models\FundUsageLog;
use App\Models\CharityDocument;
use App\Models\DonationChannel;
use Illuminate\Support\Facades\DB;

echo "🧹 Cleaning all non-admin accounts and associated data...\n\n";

// Start transaction
DB::beginTransaction();

try {
    // Count before deletion
    $donorCount = User::where('role', 'donor')->count();
    $charityAdminCount = User::where('role', 'charity_admin')->count();
    $adminCount = User::where('role', 'admin')->count();

    echo "📊 Before cleanup:\n";
    echo "   Donors: $donorCount\n";
    echo "   Charity Admins: $charityAdminCount\n";
    echo "   Admins: $adminCount\n\n";

    // Delete associated data first (due to foreign key constraints)

    // 1. Delete charity posts
    $charityPostsDeleted = CharityPost::count();
    CharityPost::query()->delete();
    echo "✅ Deleted $charityPostsDeleted charity posts\n";

    // 2. Delete fund usage logs
    $fundLogsDeleted = FundUsageLog::count();
    FundUsageLog::query()->delete();
    echo "✅ Deleted $fundLogsDeleted fund usage logs\n";

    // 3. Delete donations
    $donationsDeleted = Donation::count();
    Donation::query()->delete();
    echo "✅ Deleted $donationsDeleted donations\n";

    // 4. Delete campaigns
    $campaignsDeleted = Campaign::count();
    Campaign::query()->delete();
    echo "✅ Deleted $campaignsDeleted campaigns\n";

    // 5. Delete charity documents
    $charityDocumentsDeleted = CharityDocument::count();
    CharityDocument::query()->delete();
    echo "✅ Deleted $charityDocumentsDeleted charity documents\n";

    // 6. Delete donation channels
    $donationChannelsDeleted = DonationChannel::count();
    DonationChannel::query()->delete();
    echo "✅ Deleted $donationChannelsDeleted donation channels\n";

    // 7. Delete charities
    $charitiesDeleted = Charity::count();
    Charity::query()->delete();
    echo "✅ Deleted $charitiesDeleted charities\n";

    // 8. Finally, delete donor and charity admin users
    $donorsDeleted = User::where('role', 'donor')->delete();
    $charityAdminsDeleted = User::where('role', 'charity_admin')->delete();

    echo "✅ Deleted $donorsDeleted donors\n";
    echo "✅ Deleted $charityAdminsDeleted charity admins\n";

    // Verify admin accounts are preserved
    $remainingAdmins = User::where('role', 'admin')->count();

    echo "\n📊 After cleanup:\n";
    echo "   Remaining admins: $remainingAdmins\n";
    echo "   Total users: " . User::count() . "\n";
    echo "   Total charities: " . Charity::count() . "\n";
    echo "   Total campaigns: " . Campaign::count() . "\n";
    echo "   Total donations: " . Donation::count() . "\n";

    DB::commit();

    echo "\n🎉 Cleanup completed successfully!\n";
    echo "\n📋 Summary:\n";
    echo "   • All donor accounts deleted\n";
    echo "   • All charity admin accounts deleted\n";
    echo "   • All associated data removed\n";
    echo "   • All admin accounts preserved\n";
    echo "\n✅ Database is now clean!\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ Error during cleanup: " . $e->getMessage() . "\n";
    exit(1);
}
