# Test Backend API Connectivity
Write-Host "🔧 Testing Backend API Connectivity..." -ForegroundColor Yellow
Write-Host ""

# Test if backend server is running
Write-Host "1. Testing backend server (http://127.0.0.1:8000)..." -ForegroundColor Cyan
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000" -UseBasicParsing -TimeoutSec 5 -ErrorAction Stop
    Write-Host "   ✅ Backend server is running" -ForegroundColor Green
} catch {
    Write-Host "   ❌ Backend server is not running" -ForegroundColor Red
    Write-Host "   💡 Run: cd capstone_backend && php artisan serve" -ForegroundColor Yellow
    exit 1
}

# Test API ping endpoint
Write-Host ""
Write-Host "2. Testing API ping endpoint..." -ForegroundColor Cyan
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/ping" -UseBasicParsing -TimeoutSec 5 -ErrorAction Stop
    $content = $response.Content
    if ($content -like "*ok*") {
        Write-Host "   ✅ API ping endpoint working" -ForegroundColor Green
    } else {
        Write-Host "   ❌ API ping endpoint returned unexpected content" -ForegroundColor Red
    }
} catch {
    Write-Host "   ❌ API ping endpoint failed" -ForegroundColor Red
}

# Test metrics endpoint (public)
Write-Host ""
Write-Host "3. Testing metrics endpoint..." -ForegroundColor Cyan
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/metrics" -UseBasicParsing -TimeoutSec 5 -ErrorAction Stop
    $content = $response.Content
    if ($content -like "*total_*") {
        Write-Host "   ✅ Metrics endpoint working" -ForegroundColor Green
    } else {
        Write-Host "   ❌ Metrics endpoint returned unexpected content" -ForegroundColor Red
    }
} catch {
    Write-Host "   ❌ Metrics endpoint failed" -ForegroundColor Red
}

Write-Host ""
Write-Host "✅ Backend API tests complete!" -ForegroundColor Green
Write-Host ""
Write-Host "If all tests pass, try creating a post again." -ForegroundColor Yellow
Write-Host "If posting still fails, check the browser console for detailed error messages." -ForegroundColor Yellow
