Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Capstone Backend Database Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$backendPath = "c:\Users\sagan\Capstone\capstone_backend"
$envPath = "$backendPath\.env"

Set-Location $backendPath

# Step 1: Ensure .env file exists
Write-Host "[1/4] Checking .env file..." -ForegroundColor Yellow
if (-Not (Test-Path $envPath)) {
    Write-Host "Creating .env file from .env.example..." -ForegroundColor Yellow
    Copy-Item "$backendPath\.env.example" $envPath
    Write-Host ".env file created" -ForegroundColor Green
} else {
    Write-Host ".env file exists" -ForegroundColor Green
}

Write-Host ""

# Step 2: Generate APP_KEY if needed
Write-Host "[2/4] Checking APP_KEY..." -ForegroundColor Yellow
php artisan key:generate --ansi --force

Write-Host ""

# Step 3: Create database using PHP/Laravel
Write-Host "[3/4] Creating database 'capstone_db'..." -ForegroundColor Yellow
Write-Host "Note: Make sure MySQL is running and root user has no password (or update .env)" -ForegroundColor Cyan

$createDbScript = @'
<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$dbname = "capstone_db";

try {
    $conn = new PDO("mysql:host=$host", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $conn->exec("DROP DATABASE IF EXISTS $dbname");
    $conn->exec("CREATE DATABASE $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    echo "Database created successfully\n";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
'@

Set-Content -Path "$backendPath\create_db.php" -Value $createDbScript
php "$backendPath\create_db.php"

if ($LASTEXITCODE -ne 0) {
    Write-Host "Error creating database. Make sure MySQL is running." -ForegroundColor Red
    exit 1
}

Remove-Item "$backendPath\create_db.php" -ErrorAction SilentlyContinue

Write-Host ""

# Step 4: Run migrations and seeders
Write-Host "[4/4] Running Laravel migrations and seeders..." -ForegroundColor Yellow

php artisan config:clear
php artisan cache:clear
php artisan migrate:fresh --seed --force

if ($LASTEXITCODE -ne 0) {
    Write-Host "Error running migrations." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Demo accounts created:" -ForegroundColor Cyan
Write-Host "  Admin:   admin@example.com / password" -ForegroundColor White
Write-Host "  Donor:   donor@example.com / password" -ForegroundColor White
Write-Host "  Charity: charity@example.com / password" -ForegroundColor White

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  Database Setup Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "1. Start the backend server: php artisan serve" -ForegroundColor White
Write-Host "2. Backend will be available at http://127.0.0.1:8000" -ForegroundColor White
Write-Host ""
