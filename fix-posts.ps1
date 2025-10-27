# Fix Charity Posts Issue
Write-Host "🔧 Fixing Charity Posts Creation Issue..." -ForegroundColor Yellow
Write-Host ""

# Navigate to backend
Set-Location -Path (Join-Path $PSScriptRoot "capstone_backend")

Write-Host "1. Running charity posts migration..." -ForegroundColor Cyan
php artisan migrate

Write-Host "2. Checking if storage link exists..." -ForegroundColor Cyan
php artisan storage:link

Write-Host "3. Clearing application cache..." -ForegroundColor Cyan
php artisan cache:clear
php artisan config:clear
php artisan route:clear

Write-Host ""
Write-Host "✅ Posts system should now work!" -ForegroundColor Green
Write-Host ""
Write-Host "🎯 Test Steps:" -ForegroundColor Yellow
Write-Host "   1. Login as charity admin" -ForegroundColor White
Write-Host "   2. Go to Posts & Updates section" -ForegroundColor White
Write-Host "   3. Create a new post with title and content" -ForegroundColor White
Write-Host "   4. Optionally add an image" -ForegroundColor White
Write-Host "   5. Click 'Publish Post' or 'Save Draft'" -ForegroundColor White
Write-Host ""
Write-Host "🔍 If still failing, check:" -ForegroundColor Cyan
Write-Host "   • Backend server is running" -ForegroundColor White
Write-Host "   • Database connection is working" -ForegroundColor White
Write-Host "   • User is logged in as charity_admin" -ForegroundColor White
Write-Host "   • Charity record exists for the user" -ForegroundColor White
Write-Host ""
Write-Host "Press any key to continue..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
