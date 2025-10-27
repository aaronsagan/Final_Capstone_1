# Test Donation System
# This script tests if donations are being saved and can be retrieved

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Testing Donation System" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# Test 1: Check if donations table has data
Write-Host "Test 1: Checking donations in database..." -ForegroundColor Yellow
Set-Location capstone_backend

$query = @"
SELECT 
    d.id,
    d.amount,
    d.status,
    d.donated_at,
    COALESCE(u.name, 'Anonymous') as donor_name,
    c.name as charity_name,
    COALESCE(cam.title, 'General Donation') as campaign_title
FROM donations d
LEFT JOIN users u ON d.donor_id = u.id
INNER JOIN charities c ON d.charity_id = c.id
LEFT JOIN campaigns cam ON d.campaign_id = cam.id
ORDER BY d.created_at DESC
LIMIT 10;
"@

php artisan tinker --execute="echo DB::select('$query') | json_encode(JSON_PRETTY_PRINT);"

Write-Host ""

# Test 2: Check donation API endpoint
Write-Host "Test 2: Testing API endpoint..." -ForegroundColor Yellow
Write-Host "You need to test this manually:" -ForegroundColor Cyan
Write-Host "1. Login as charity admin" -ForegroundColor White
Write-Host "2. Open DevTools (F12) > Network tab" -ForegroundColor White
Write-Host "3. Navigate to Donation Management page" -ForegroundColor White
Write-Host "4. Look for: GET /api/charities/{id}/donations" -ForegroundColor White
Write-Host "5. Check the response contains donation data" -ForegroundColor White
Write-Host ""

# Test 3: Check if the service file exists
Write-Host "Test 3: Verifying service files..." -ForegroundColor Yellow
Set-Location ..

if (Test-Path "capstone_frontend\src\services\donations.ts") {
    Write-Host "✓ donations.ts service exists" -ForegroundColor Green
} else {
    Write-Host "✗ donations.ts service NOT found" -ForegroundColor Red
}

if (Test-Path "capstone_frontend\src\pages\charity\DonationManagement.tsx") {
    Write-Host "✓ DonationManagement.tsx exists" -ForegroundColor Green
    
    # Check if it's connected to the service
    $content = Get-Content "capstone_frontend\src\pages\charity\DonationManagement.tsx" -Raw
    if ($content -match "donationsService") {
        Write-Host "✓ DonationManagement.tsx is connected to donationsService" -ForegroundColor Green
    } else {
        Write-Host "✗ DonationManagement.tsx NOT connected to donationsService" -ForegroundColor Red
    }
    
    if ($content -match "loadDonations") {
        Write-Host "✓ loadDonations function exists" -ForegroundColor Green
    } else {
        Write-Host "✗ loadDonations function NOT found" -ForegroundColor Red
    }
} else {
    Write-Host "✗ DonationManagement.tsx NOT found" -ForegroundColor Red
}

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Quick Testing Guide" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "To verify donations are displaying:" -ForegroundColor Yellow
Write-Host "1. Start backend: cd capstone_backend && php artisan serve" -ForegroundColor White
Write-Host "2. Start frontend: cd capstone_frontend && npm run dev" -ForegroundColor White
Write-Host "3. Login as charity admin" -ForegroundColor White
Write-Host "4. Navigate to: /charity/donations" -ForegroundColor White
Write-Host "5. You should see donations from the database" -ForegroundColor White
Write-Host ""
Write-Host "If no donations show:" -ForegroundColor Yellow
Write-Host "- Check browser console (F12) for errors" -ForegroundColor White
Write-Host "- Check Network tab for API call to /api/charities/{id}/donations" -ForegroundColor White
Write-Host "- Verify your charity account has donations in database" -ForegroundColor White
Write-Host ""
