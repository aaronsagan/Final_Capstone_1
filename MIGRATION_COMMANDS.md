# MySQL Migration - Copy & Paste Commands

## Quick Reference: All Commands in Order

### Step 1: Update .env File (MANUAL)
Open `capstone_backend\.env` and change:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=capstone_db
DB_USERNAME=root
DB_PASSWORD=
```

---

### Step 2: Start MySQL Service
```powershell
# Check status
Get-Service -Name MySQL* | Select-Object Name, Status

# Start if needed
Start-Service -Name MySQL80
```

---

### Step 3: Create Database
```powershell
# Option A: Using mysql command
mysql -u root -e "CREATE DATABASE IF NOT EXISTS capstone_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Option B: Interactive
mysql -u root
```
Then in MySQL prompt:
```sql
CREATE DATABASE IF NOT EXISTS capstone_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

---

### Step 4: Clear Laravel Caches
```powershell
cd capstone_backend
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

### Step 5: Test Connection
```powershell
php artisan db:show
```

---

### Step 6: Run Migrations
```powershell
# Option A: Fresh migration (deletes all data)
php artisan migrate:fresh

# Option B: Regular migration (preserves data if possible)
php artisan migrate
```

---

### Step 7: Seed Database (Optional)
```powershell
# Seed all
php artisan db:seed

# Or seed specific seeders
php artisan db:seed --class=UsersSeeder
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=DemoDataSeeder
```

---

### Step 8: Verify
```powershell
# Check migration status
php artisan migrate:status

# Test in tinker
php artisan tinker
```
In tinker:
```php
User::count()
DB::connection()->getDatabaseName()
exit
```

---

### Step 9: Start Servers
```powershell
# Backend (in capstone_backend directory)
php artisan serve

# Frontend (in new terminal, in capstone_frontend directory)
cd ../capstone_frontend
npm run dev
```

---

## One-Line Complete Migration

After updating `.env` file:

```powershell
cd capstone_backend; mysql -u root -e "CREATE DATABASE IF NOT EXISTS capstone_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"; php artisan config:clear; php artisan cache:clear; php artisan migrate:fresh --seed; php artisan db:show
```

---

## Troubleshooting Commands

### Check MySQL is Running
```powershell
Get-Service -Name MySQL*
netstat -an | findstr :3306
```

### Test MySQL Connection Directly
```powershell
mysql -u root -e "SELECT VERSION();"
```

### Check PHP MySQL Extension
```powershell
php -m | findstr pdo_mysql
php -m | findstr mysqli
```

### View Laravel Logs
```powershell
Get-Content capstone_backend\storage\logs\laravel.log -Tail 50
```

### Reset MySQL Root Password (if needed)
```powershell
mysql -u root
```
```sql
ALTER USER 'root'@'localhost' IDENTIFIED BY '';
FLUSH PRIVILEGES;
EXIT;
```

### Check Database Exists
```powershell
mysql -u root -e "SHOW DATABASES;"
```

### Check Tables in Database
```powershell
mysql -u root capstone_db -e "SHOW TABLES;"
```

### Drop and Recreate Database (CAUTION: Deletes all data)
```powershell
mysql -u root -e "DROP DATABASE IF EXISTS capstone_db; CREATE DATABASE capstone_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

---

## Automated Script

Run the PowerShell script for guided migration:
```powershell
.\migrate-to-mysql.ps1
```

---

## Rollback to SQLite

If you need to go back:

```powershell
# 1. Update .env
# Change DB_CONNECTION=mysql to DB_CONNECTION=sqlite

# 2. Clear caches
cd capstone_backend
php artisan config:clear
php artisan cache:clear

# 3. Ensure SQLite file exists
New-Item -Path database\database.sqlite -ItemType File -Force

# 4. Migrate
php artisan migrate:fresh --seed
```

---

## Common Error Solutions

### "Connection refused"
```powershell
Start-Service -Name MySQL80
```

### "Access denied"
```powershell
# Update .env with correct password or reset MySQL password
```

### "Unknown database"
```powershell
mysql -u root -e "CREATE DATABASE capstone_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### "PDO driver not found"
```powershell
# Edit php.ini and uncomment:
# extension=pdo_mysql
# Then restart PHP
```

### "Key too long" error
Add to `app/Providers/AppServiceProvider.php`:
```php
use Illuminate\Support\Facades\Schema;

public function boot()
{
    Schema::defaultStringLength(191);
}
```
