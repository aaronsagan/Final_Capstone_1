# SQLite to MySQL Migration Script
# Run this script after updating your .env file

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "SQLite to MySQL Migration Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if we're in the right directory
if (-not (Test-Path "capstone_backend")) {
    Write-Host "ERROR: Please run this script from the Capstone root directory" -ForegroundColor Red
    exit 1
}

# Navigate to backend
Set-Location capstone_backend

Write-Host "[1/8] Checking MySQL service..." -ForegroundColor Yellow
$mysqlService = Get-Service -Name MySQL* -ErrorAction SilentlyContinue
if ($mysqlService) {
    if ($mysqlService.Status -eq "Running") {
        Write-Host "✓ MySQL service is running" -ForegroundColor Green
    } else {
        Write-Host "⚠ MySQL service is not running. Attempting to start..." -ForegroundColor Yellow
        try {
            Start-Service -Name $mysqlService.Name
            Write-Host "✓ MySQL service started" -ForegroundColor Green
        } catch {
            Write-Host "✗ Failed to start MySQL service. Please start it manually." -ForegroundColor Red
            exit 1
        }
    }
} else {
    Write-Host "⚠ MySQL service not found. Please ensure MySQL is installed." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "[2/8] Checking .env file..." -ForegroundColor Yellow
if (Test-Path ".env") {
    $envContent = Get-Content ".env" -Raw
    if ($envContent -match "DB_CONNECTION=mysql") {
        Write-Host "✓ .env file configured for MySQL" -ForegroundColor Green
    } else {
        Write-Host "⚠ .env file not configured for MySQL" -ForegroundColor Yellow
        Write-Host "Please update your .env file with MySQL configuration:" -ForegroundColor Yellow
        Write-Host ""
        Write-Host "DB_CONNECTION=mysql" -ForegroundColor White
        Write-Host "DB_HOST=127.0.0.1" -ForegroundColor White
        Write-Host "DB_PORT=3306" -ForegroundColor White
        Write-Host "DB_DATABASE=capstone_db" -ForegroundColor White
        Write-Host "DB_USERNAME=root" -ForegroundColor White
        Write-Host "DB_PASSWORD=" -ForegroundColor White
        Write-Host ""
        $continue = Read-Host "Have you updated the .env file? (y/n)"
        if ($continue -ne "y") {
            Write-Host "Please update .env file and run this script again." -ForegroundColor Red
            exit 1
        }
    }
} else {
    Write-Host "✗ .env file not found. Please create it from .env.example" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "[3/8] Creating database..." -ForegroundColor Yellow
Write-Host "Attempting to create database 'capstone_db'..." -ForegroundColor Gray
$createDbCommand = "CREATE DATABASE IF NOT EXISTS capstone_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
try {
    $result = mysql -u root -e $createDbCommand 2>&1
    Write-Host "✓ Database created or already exists" -ForegroundColor Green
} catch {
    Write-Host "⚠ Could not create database automatically. Please create it manually:" -ForegroundColor Yellow
    Write-Host "  mysql -u root" -ForegroundColor White
    Write-Host "  CREATE DATABASE capstone_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" -ForegroundColor White
}

Write-Host ""
Write-Host "[4/8] Clearing configuration cache..." -ForegroundColor Yellow
php artisan config:clear
Write-Host "✓ Configuration cache cleared" -ForegroundColor Green

Write-Host ""
Write-Host "[5/8] Clearing application cache..." -ForegroundColor Yellow
php artisan cache:clear
Write-Host "✓ Application cache cleared" -ForegroundColor Green

Write-Host ""
Write-Host "[6/8] Testing database connection..." -ForegroundColor Yellow
try {
    $dbShow = php artisan db:show 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Database connection successful" -ForegroundColor Green
    } else {
        Write-Host "✗ Database connection failed" -ForegroundColor Red
        Write-Host $dbShow -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "✗ Database connection failed" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "[7/8] Running migrations..." -ForegroundColor Yellow
Write-Host "This will create all database tables..." -ForegroundColor Gray
$migrateChoice = Read-Host "Run fresh migrations (will delete existing data)? (y/n)"
if ($migrateChoice -eq "y") {
    php artisan migrate:fresh
    Write-Host "✓ Migrations completed" -ForegroundColor Green
} else {
    php artisan migrate
    Write-Host "✓ Migrations completed" -ForegroundColor Green
}

Write-Host ""
Write-Host "[8/8] Seeding database..." -ForegroundColor Yellow
$seedChoice = Read-Host "Run database seeders? (y/n)"
if ($seedChoice -eq "y") {
    php artisan db:seed
    Write-Host "✓ Database seeded" -ForegroundColor Green
} else {
    Write-Host "⊘ Skipped seeding" -ForegroundColor Gray
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Migration Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Start your Laravel backend: php artisan serve" -ForegroundColor White
Write-Host "2. Start your frontend: cd ../capstone_frontend && npm run dev" -ForegroundColor White
Write-Host "3. Test your application" -ForegroundColor White
Write-Host ""
Write-Host "If you encounter issues, check MYSQL_MIGRATION_GUIDE.md" -ForegroundColor Gray

# Return to original directory
Set-Location ..
