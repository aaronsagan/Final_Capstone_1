<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Charity;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\CharityPost;
use App\Models\FundUsageLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CleanNonAdminAccounts extends Command
{
    protected $signature = 'db:clean-non-admin';
    protected $description = 'Delete all donor and charity admin accounts and associated data, keep only admin accounts';

    public function handle()
    {
        $this->info('🧹 Cleaning all non-admin accounts and associated data...');
        $this->info('');

        DB::beginTransaction();
        try {
            // Count before deletion
            $donorCount = User::where('role', 'donor')->count();
            $charityAdminCount = User::where('role', 'charity_admin')->count();
            $adminCount = User::where('role', 'admin')->count();

            // Also count demo accounts by email pattern
            $demoDonorCount = User::where('email', 'like', '%@example.com')->where('role', 'donor')->count();
            $demoCharityAdminCount = User::where('email', 'like', '%@example.com')->where('role', 'charity_admin')->count();

            $this->info("📊 Before cleanup:");
            $this->info("   Donors: $donorCount");
            $this->info("   Charity Admins: $charityAdminCount");
            $this->info("   Admins: $adminCount");
            $this->info("   Demo Donors: $demoDonorCount");
            $this->info("   Demo Charity Admins: $demoCharityAdminCount");
            $this->info('');

            // Delete associated data first (due to foreign key constraints)

            // 1. Delete charity posts
            $charityPostsDeleted = CharityPost::count();
            CharityPost::query()->delete();
            $this->info("✅ Deleted $charityPostsDeleted charity posts");

            // 2. Delete fund usage logs
            $fundLogsDeleted = FundUsageLog::count();
            FundUsageLog::query()->delete();
            $this->info("✅ Deleted $fundLogsDeleted fund usage logs");

            // 3. Delete donations (this will also delete related data due to cascade)
            $donationsDeleted = Donation::count();
            Donation::query()->delete();
            $this->info("✅ Deleted $donationsDeleted donations");

            // 4. Delete campaigns
            $campaignsDeleted = Campaign::count();
            Campaign::query()->delete();
            $this->info("✅ Deleted $campaignsDeleted campaigns");

            // 5. Delete charities (this should delete charity documents and channels due to cascade)
            $charitiesDeleted = Charity::count();
            Charity::query()->delete();
            $this->info("✅ Deleted $charitiesDeleted charities");

            // 6. Finally, delete donor and charity admin users (including demo accounts)
            $donorsDeleted = User::where('role', 'donor')->delete();
            $charityAdminsDeleted = User::where('role', 'charity_admin')->delete();

            $this->info("✅ Deleted $donorsDeleted donors");
            $this->info("✅ Deleted $charityAdminsDeleted charity admins");

            // Verify admin accounts are preserved
            $remainingAdmins = User::where('role', 'admin')->count();
            $remainingUsers = User::count();

            $this->info('');
            $this->info("📊 After cleanup:");
            $this->info("   Remaining users: $remainingUsers");
            $this->info("   Remaining admins: $remainingAdmins");
            $this->info("   Remaining charities: " . Charity::count());
            $this->info("   Remaining campaigns: " . Campaign::count());
            $this->info("   Remaining donations: " . Donation::count());
            $this->info("   Remaining posts: " . CharityPost::count());

            DB::commit();

            $this->info('');
            $this->info('🎉 Cleanup completed successfully!');
            $this->info('');
            $this->info('📋 Summary:');
            $this->info('   • All donor accounts deleted');
            $this->info('   • All charity admin accounts deleted');
            $this->info('   • All associated data removed (campaigns, donations, posts, fund logs)');
            $this->info('   • All charity documents and donation channels removed');
            $this->info('   • All system admin accounts preserved');
            $this->info('');
            $this->info('✅ Database is now clean and ready for fresh testing!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error during cleanup: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
