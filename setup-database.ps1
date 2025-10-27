# Setup Database Script
# This script configures SQLite database and runs migrations with seeders

Write-Host "=== Database Setup Script ===" -ForegroundColor Cyan
Write-Host ""

$backendPath = "c:\Users\sagan\Capstone\capstone_backend"
$envPath = "$backendPath\.env"
$dbPath = "$backendPath\database\database.sqlite"

# Step 1: Check if .env exists
if (-Not (Test-Path $envPath)) {
    Write-Host "Creating .env file from .env.example..." -ForegroundColor Yellow
    Copy-Item "$backendPath\.env.example" $envPath
}

# Step 2: Update .env to use SQLite
Write-Host "Configuring database to use SQLite..." -ForegroundColor Yellow
$envContent = Get-Content $envPath -Raw

# Replace DB_CONNECTION if it's set to mysql
$envContent = $envContent -replace 'DB_CONNECTION=mysql', 'DB_CONNECTION=sqlite'

# Comment out MySQL settings if they exist
$envContent = $envContent -replace '^(DB_HOST=)', '# $1'
$envContent = $envContent -replace '^(DB_PORT=)', '# $1'
$envContent = $envContent -replace '^(DB_DATABASE=(?!sqlite))', '# $1'
$envContent = $envContent -replace '^(DB_USERNAME=)', '# $1'
$envContent = $envContent -replace '^(DB_PASSWORD=)', '# $1'

Set-Content $envPath $envContent

# Step 3: Create SQLite database file
Write-Host "Creating SQLite database file..." -ForegroundColor Yellow
if (-Not (Test-Path $dbPath)) {
    New-Item -Path $dbPath -ItemType File -Force | Out-Null
    Write-Host "✓ Database file created: $dbPath" -ForegroundColor Green
} else {
    Write-Host "✓ Database file already exists" -ForegroundColor Green
}

# Step 4: Generate APP_KEY if not set
Write-Host "Checking APP_KEY..." -ForegroundColor Yellow
Set-Location $backendPath
php artisan key:generate --ansi

# Step 5: Run migrations with seeders
Write-Host ""
Write-Host "Running migrations and seeders..." -ForegroundColor Yellow
Write-Host "This will create all tables and insert demo accounts:" -ForegroundColor Cyan
Write-Host "  - admin@example.com (password: password)" -ForegroundColor White
Write-Host "  - donor@example.com (password: password)" -ForegroundColor White
Write-Host "  - charityadmin@example.com (password: password)" -ForegroundColor White
Write-Host ""

php artisan migrate:fresh --seed --force

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "=== Setup Complete! ===" -ForegroundColor Green
    Write-Host ""
    Write-Host "Demo accounts created:" -ForegroundColor Cyan
    Write-Host "  Admin:   admin@example.com / password" -ForegroundColor White
    Write-Host "  Donor:   donor@example.com / password" -ForegroundColor White
    Write-Host "  Charity: charityadmin@example.com / password" -ForegroundColor White
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "=== Setup Failed ===" -ForegroundColor Red
    Write-Host "Please check the error messages above." -ForegroundColor Yellow
}
