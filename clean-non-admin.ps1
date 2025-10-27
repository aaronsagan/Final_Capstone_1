# Clean All Non-Admin Accounts and Data
Write-Host "🧹 Cleaning all donor and charity admin accounts..." -ForegroundColor Yellow
Write-Host ""

# Navigate to backend
cd capstone_backend

Write-Host "1. Running the cleanup command..." -ForegroundColor Cyan
php artisan db:clean-non-admin

Write-Host ""
Write-Host "2. Verifying cleanup..." -ForegroundColor Cyan

# Check remaining data
php artisan tinker --execute="
echo 'Remaining Users: ' . App\Models\User::count() . PHP_EOL;
echo 'Remaining Charities: ' . App\Models\Charity::count() . PHP_EOL;
echo 'Remaining Campaigns: ' . App\Models\Campaign::count() . PHP_EOL;
echo 'Remaining Donations: ' . App\Models\Donation::count() . PHP_EOL;
echo 'Remaining Posts: ' . App\Models\CharityPost::count() . PHP_EOL;
echo 'Admin Users: ' . App\Models\User::where('role', 'admin')->count() . PHP_EOL;
"

Write-Host ""
Write-Host "✅ Cleanup completed!" -ForegroundColor Green
Write-Host ""
Write-Host "📋 What was removed:" -ForegroundColor Yellow
Write-Host "   • All donor accounts and their data" -ForegroundColor White
Write-Host "   • All charity admin accounts and their data" -ForegroundColor White
Write-Host "   • All campaigns, donations, fund logs, posts" -ForegroundColor White
Write-Host "   • All charity documents and channels" -ForegroundColor White
Write-Host ""
Write-Host "🛡️ What was preserved:" -ForegroundColor Green
Write-Host "   • All system admin accounts" -ForegroundColor White
Write-Host "   • Database structure and tables" -ForegroundColor White
Write-Host ""
Write-Host "🎯 Ready for fresh testing!" -ForegroundColor Cyan
Write-Host ""
Write-Host "Press any key to continue..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
