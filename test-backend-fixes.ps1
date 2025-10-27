# Backend Fixes Test Script
# This script helps verify all backend fixes are working correctly

Write-Host "==================================" -ForegroundColor Cyan
Write-Host "Backend Fixes Test Script" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

$backendPath = "$PSScriptRoot\capstone_backend"
$baseUrl = "http://localhost:8000/api"

# Check if backend is running
Write-Host "1. Checking if backend is running..." -ForegroundColor Yellow
try {
    $null = Invoke-WebRequest -Uri "$baseUrl/ping" -Method GET -ErrorAction Stop
    Write-Host "   ✓ Backend is running!" -ForegroundColor Green
}
catch {
    Write-Host "   ✗ Backend is not running!" -ForegroundColor Red
    Write-Host "   Please start the backend first:" -ForegroundColor Yellow
    Write-Host "   cd capstone_backend" -ForegroundColor Gray
    Write-Host "   php artisan serve" -ForegroundColor Gray
    exit 1
}

Write-Host ""
Write-Host "2. Checking storage link..." -ForegroundColor Yellow
$storageLinkPath = "$backendPath\public\storage"
if (Test-Path $storageLinkPath) {
    Write-Host "   ✓ Storage link exists!" -ForegroundColor Green
}
else {
    Write-Host "   ✗ Storage link not found!" -ForegroundColor Red
    Write-Host "   Creating storage link..." -ForegroundColor Yellow
    Set-Location $backendPath
    php artisan storage:link
    Write-Host "   ✓ Storage link created!" -ForegroundColor Green
}

Write-Host ""
Write-Host "3. Checking database connection..." -ForegroundColor Yellow
Set-Location $backendPath
$null = php artisan db:show 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✓ Database connection successful!" -ForegroundColor Green
}
else {
    Write-Host "   ✗ Database connection failed!" -ForegroundColor Red
    Write-Host "   Please check your .env file and database configuration" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "4. Checking required tables..." -ForegroundColor Yellow
$tables = @("users", "charities", "campaigns", "fund_usage_logs", "charity_posts")
foreach ($table in $tables) {
    $checkTable = php artisan tinker --execute="echo \Schema::hasTable('$table') ? 'exists' : 'missing';" 2>&1
    if ($checkTable -match "exists") {
        Write-Host "   ✓ Table '$table' exists" -ForegroundColor Green
    }
    else {
        Write-Host "   ✗ Table '$table' missing" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "==================================" -ForegroundColor Cyan
Write-Host "Test Recommendations:" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "For DONOR users:" -ForegroundColor Yellow
Write-Host "  1. Login and check if user data is returned" -ForegroundColor Gray
Write-Host "  2. Update profile with name phone address" -ForegroundColor Gray
Write-Host "  3. Upload profile image" -ForegroundColor Gray
Write-Host "  4. Make a donation" -ForegroundColor Gray
Write-Host ""

Write-Host "For CHARITY ADMIN users:" -ForegroundColor Yellow
Write-Host "  1. Login and check if user and charity data is returned" -ForegroundColor Gray
Write-Host "  2. Update profile and organization details" -ForegroundColor Gray
Write-Host "  3. Upload logo and cover image" -ForegroundColor Gray
Write-Host "  4. Create a campaign with cover image" -ForegroundColor Gray
Write-Host "  5. Add fund usage log with attachment" -ForegroundColor Gray
Write-Host "  6. Create a charity post with image" -ForegroundColor Gray
Write-Host ""

Write-Host "For SYSTEM ADMIN users:" -ForegroundColor Yellow
Write-Host "  1. Login and verify admin access" -ForegroundColor Gray
Write-Host "  2. View pending charity verifications" -ForegroundColor Gray
Write-Host "  3. Approve or reject charities" -ForegroundColor Gray
Write-Host "  4. View and manage users" -ForegroundColor Gray
Write-Host ""

Write-Host "==================================" -ForegroundColor Cyan
Write-Host "API Endpoints to Test:" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Authentication:" -ForegroundColor Yellow
Write-Host "  POST   $baseUrl/auth/login" -ForegroundColor Gray
Write-Host "  GET    $baseUrl/me" -ForegroundColor Gray
Write-Host "  PUT    $baseUrl/me" -ForegroundColor Gray
Write-Host ""
Write-Host "Charities:" -ForegroundColor Yellow
Write-Host "  GET    $baseUrl/charities" -ForegroundColor Gray
Write-Host "  PUT    $baseUrl/charities/{id}" -ForegroundColor Gray
Write-Host ""
Write-Host "Campaigns:" -ForegroundColor Yellow
Write-Host "  POST   $baseUrl/charities/{id}/campaigns" -ForegroundColor Gray
Write-Host "  GET    $baseUrl/campaigns/{id}" -ForegroundColor Gray
Write-Host ""
Write-Host "Fund Usage:" -ForegroundColor Yellow
Write-Host "  POST   $baseUrl/campaigns/{id}/fund-usage" -ForegroundColor Gray
Write-Host "  GET    $baseUrl/campaigns/{id}/fund-usage" -ForegroundColor Gray
Write-Host ""
Write-Host "Charity Posts:" -ForegroundColor Yellow
Write-Host "  POST   $baseUrl/posts" -ForegroundColor Gray
Write-Host "  GET    $baseUrl/posts" -ForegroundColor Gray
Write-Host ""

Write-Host "==================================" -ForegroundColor Cyan
Write-Host "Check Laravel Logs:" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host "  Location: capstone_backend\storage\logs\laravel.log" -ForegroundColor Gray
Write-Host "  Use this to debug any errors during testing" -ForegroundColor Gray
Write-Host ""

Write-Host "==================================" -ForegroundColor Green
Write-Host "Setup Complete! Ready for Testing" -ForegroundColor Green
Write-Host "==================================" -ForegroundColor Green
