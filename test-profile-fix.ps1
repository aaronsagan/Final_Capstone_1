# Test Profile Edit Fix
# This script helps verify the donor profile edit fix is working

Write-Host "=== Testing Donor Profile Edit Fix ===" -ForegroundColor Cyan
Write-Host ""

# Check if frontend is running
Write-Host "1. Checking if frontend is running on port 5173..." -ForegroundColor Yellow
try {
    $null = Invoke-WebRequest -Uri "http://localhost:5173" -UseBasicParsing -TimeoutSec 2 -ErrorAction Stop
    Write-Host "   ✓ Frontend is running" -ForegroundColor Green
} catch {
    Write-Host "   ✗ Frontend is NOT running. Please start it with 'npm run dev'" -ForegroundColor Red
    Write-Host ""
    exit 1
}

# Check if backend is running
Write-Host "2. Checking if backend is running on port 8000..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8000/api/ping" -UseBasicParsing -TimeoutSec 2 -ErrorAction Stop
    Write-Host "   ✓ Backend is running" -ForegroundColor Green
} catch {
    Write-Host "   ✗ Backend is NOT running. Please start it with 'php artisan serve'" -ForegroundColor Red
    Write-Host ""
    exit 1
}

Write-Host ""
Write-Host "=== Manual Testing Steps ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Please follow these steps to test the fix:" -ForegroundColor White
Write-Host ""
Write-Host "1. Open browser and navigate to: http://localhost:5173/auth/login" -ForegroundColor Yellow
Write-Host ""
Write-Host "2. Login as a donor:" -ForegroundColor Yellow
Write-Host "   Email: donor@test.com" -ForegroundColor Gray
Write-Host "   Password: password" -ForegroundColor Gray
Write-Host "   (Try both WITH and WITHOUT 'Remember Me' checked)" -ForegroundColor Gray
Write-Host ""
Write-Host "3. Navigate to: http://localhost:5173/donor/profile" -ForegroundColor Yellow
Write-Host ""
Write-Host "4. Click 'Edit Profile' button" -ForegroundColor Yellow
Write-Host ""
Write-Host "5. Make changes:" -ForegroundColor Yellow
Write-Host "   - Upload a profile photo" -ForegroundColor Gray
Write-Host "   - Edit name, phone, or address" -ForegroundColor Gray
Write-Host ""
Write-Host "6. Click 'Save Changes'" -ForegroundColor Yellow
Write-Host ""
Write-Host "Expected Result:" -ForegroundColor Green
Write-Host "   ✓ Should see 'Profile updated successfully' toast message" -ForegroundColor Green
Write-Host "   ✓ Should NOT see 'Please login first' error" -ForegroundColor Green
Write-Host ""
Write-Host "7. Test password change:" -ForegroundColor Yellow
Write-Host "   - Click 'Change Password'" -ForegroundColor Gray
Write-Host "   - Enter current and new password" -ForegroundColor Gray
Write-Host "   - Click 'Change Password'" -ForegroundColor Gray
Write-Host ""
Write-Host "Expected Result:" -ForegroundColor Green
Write-Host "   ✓ Should see 'Password changed successfully' toast message" -ForegroundColor Green
Write-Host "   ✓ Should NOT see 'Please login first' error" -ForegroundColor Green
Write-Host ""
Write-Host "=== Additional Tests ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Test with Charity Admin:" -ForegroundColor Yellow
Write-Host "   - Login as: charity@test.com / password" -ForegroundColor Gray
Write-Host "   - Navigate to: http://localhost:5173/charity/profile" -ForegroundColor Gray
Write-Host "   - Try editing organization profile" -ForegroundColor Gray
Write-Host ""
Write-Host "Test with System Admin:" -ForegroundColor Yellow
Write-Host "   - Login as: admin@test.com / password" -ForegroundColor Gray
Write-Host "   - Navigate to: http://localhost:5173/admin" -ForegroundColor Gray
Write-Host "   - Try approving/rejecting charities or suspending users" -ForegroundColor Gray
Write-Host ""
Write-Host "=== Fix Summary ===" -ForegroundColor Cyan
Write-Host "Fixed token retrieval in:" -ForegroundColor White
Write-Host "   ✓ DonorProfile.tsx (handleSave, handleChangePassword)" -ForegroundColor Green
Write-Host "   ✓ OrganizationProfile.tsx (handleSave)" -ForegroundColor Green
Write-Host "   ✓ CharitySettings.tsx (handleSaveProfile)" -ForegroundColor Green
Write-Host "   ✓ Admin Dashboard.tsx (all admin actions)" -ForegroundColor Green
Write-Host ""
Write-Host "All files now check both localStorage AND sessionStorage for 'auth_token'" -ForegroundColor White
Write-Host ""
