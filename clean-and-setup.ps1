# Clean Database and Setup Fresh Environment
Write-Host "🧹 Cleaning Database and Setting Up Fresh Environment..." -ForegroundColor Yellow
Write-Host ""

# Navigate to backend
cd capstone_backend

Write-Host "1. Dropping all tables and recreating..." -ForegroundColor Cyan
php artisan migrate:fresh

Write-Host "2. Running new migrations..." -ForegroundColor Cyan
php artisan migrate

Write-Host "3. Creating fresh admin account only..." -ForegroundColor Cyan
php artisan db:clean

Write-Host ""
Write-Host "✅ Database cleaned successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "🎯 Fresh Environment Ready:" -ForegroundColor Yellow
Write-Host "   • All seeded data removed" -ForegroundColor White
Write-Host "   • No demo accounts, donations, campaigns, or posts" -ForegroundColor White
Write-Host "   • Only system admin account created" -ForegroundColor White
Write-Host ""
Write-Host "🔑 Admin Login Credentials:" -ForegroundColor Cyan
Write-Host "   Email: admin@charityhub.com" -ForegroundColor White
Write-Host "   Password: admin123" -ForegroundColor White
Write-Host ""
Write-Host "🚀 Next Steps:" -ForegroundColor Yellow
Write-Host "   1. Start your frontend and backend servers" -ForegroundColor White
Write-Host "   2. Register real charities through the registration form" -ForegroundColor White
Write-Host "   3. Login as admin to verify charities" -ForegroundColor White
Write-Host "   4. Verified charities will appear on public pages" -ForegroundColor White
Write-Host "   5. Donors can see charity posts in news feed" -ForegroundColor White
Write-Host ""
Write-Host "Press any key to continue..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
