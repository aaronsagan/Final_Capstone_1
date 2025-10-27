# Navigate to backend directory and run the demo users command
cd capstone_backend
php artisan demo:users

Write-Host ""
Write-Host "Password has been fixed for iflicdmi.staf@gmail.com" -ForegroundColor Green
Write-Host "You can now login with:" -ForegroundColor Yellow
Write-Host "Email: iflicdmi.staf@gmail.com" -ForegroundColor Cyan
Write-Host "Password: password" -ForegroundColor Cyan
Write-Host ""
Write-Host "Press any key to continue..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
