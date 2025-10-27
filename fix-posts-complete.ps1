# Comprehensive Fix for Post Creation
Write-Host "🔧 Running comprehensive fix for post creation..." -ForegroundColor Yellow
Write-Host ""

# Navigate to backend directory
# Set the backend directory path
$backendDir = Join-Path -Path $PSScriptRoot -ChildPath "capstone_backend"
Set-Location -Path $backendDir

Write-Host "1. Running migrations..." -ForegroundColor Cyan
php artisan migrate

Write-Host "2. Creating storage link..." -ForegroundColor Cyan
php artisan storage:link

Write-Host "3. Clearing all caches..." -ForegroundColor Cyan
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

Write-Host "4. Checking if charity_posts table exists..." -ForegroundColor Cyan
php artisan tinker --execute="echo Schema::hasTable('charity_posts') ? '✅ Table exists' : '❌ Table missing';"

Write-Host ""
Write-Host "5. Optional: Clean non-admin accounts (removes all test data)..." -ForegroundColor Cyan
$cleanData = Read-Host "Clean all donor/charity admin accounts? (y/N)"
if ($cleanData -eq 'y' -or $cleanData -eq 'Y') {
    php artisan db:clean-non-admin
}

Write-Host ""
Write-Host "✅ Database setup complete!" -ForegroundColor Green
Write-Host ""
Write-Host "🎯 Next Steps:" -ForegroundColor Yellow
Write-Host "   1. Start the backend server: php artisan serve" -ForegroundColor White
Write-Host "   2. Login as charity admin in the frontend" -ForegroundColor White
Write-Host "   3. Go to Posts & Updates section" -ForegroundColor White
Write-Host "   4. Create a post with title and content" -ForegroundColor White
Write-Host "   5. Check browser console for any API errors" -ForegroundColor White
Write-Host ""
Write-Host "🔍 If still failing, run test script:" -ForegroundColor Cyan
Write-Host "   .\test-backend-api.ps1" -ForegroundColor White
Write-Host ""
Write-Host "Press any key to continue..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
