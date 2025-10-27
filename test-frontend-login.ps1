# Test Frontend Login
Write-Host "=== Testing Frontend Login Flow ===" -ForegroundColor Cyan
Write-Host ""

# Test 1: Check if backend is running
Write-Host "1. Checking backend API..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/ping" -Method GET -ErrorAction Stop
    Write-Host "   ✓ Backend is running" -ForegroundColor Green
    Write-Host "   Response: $($response | ConvertTo-Json -Compress)" -ForegroundColor Gray
} catch {
    Write-Host "   ❌ Backend is NOT running!" -ForegroundColor Red
    Write-Host "   Start it with: cd capstone_backend; php artisan serve" -ForegroundColor Yellow
    exit 1
}

Write-Host ""

# Test 2: Test login endpoint directly
Write-Host "2. Testing login endpoint..." -ForegroundColor Yellow
try {
    $loginBody = @{
        email = "admin@example.com"
        password = "password"
    } | ConvertTo-Json

    $loginResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/auth/login" `
        -Method POST `
        -Headers @{"Content-Type"="application/json"; "Accept"="application/json"} `
        -Body $loginBody `
        -ErrorAction Stop
    
    Write-Host "   ✓ Login successful!" -ForegroundColor Green
    Write-Host "   Token: $($loginResponse.token.Substring(0, 20))..." -ForegroundColor Gray
    Write-Host "   User: $($loginResponse.user.email) ($($loginResponse.user.role))" -ForegroundColor Gray
} catch {
    Write-Host "   ❌ Login failed!" -ForegroundColor Red
    Write-Host "   Error: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Test 3: Summary
Write-Host ""
Write-Host "=== Summary ===" -ForegroundColor Cyan
Write-Host "Backend API: Working" -ForegroundColor Green
Write-Host "Login Endpoint: Working" -ForegroundColor Green
Write-Host "Credentials: admin@example.com / password" -ForegroundColor White
Write-Host ""
Write-Host "If login still fails in browser, check:" -ForegroundColor Yellow
Write-Host "  1. Browser console (F12) for errors" -ForegroundColor White
Write-Host "  2. Network tab to see the actual request" -ForegroundColor White
Write-Host "  3. Make sure frontend .env.local is configured" -ForegroundColor White
