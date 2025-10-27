# SQLite to MySQL Migration Guide

## Complete Step-by-Step Migration Process

This guide covers the complete migration from SQLite to MySQL for your Laravel backend.

---

## Prerequisites

### 1. Ensure MySQL is Running
```powershell
# Check if MySQL service is running
Get-Service -Name MySQL* | Select-Object Name, Status

# If not running, start it
Start-Service -Name MySQL80  # Adjust name based on your MySQL version
```

### 2. Create Database
```powershell
# Login to MySQL
mysql -u root

# In MySQL prompt, create database
CREATE DATABASE capstone_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

---

## Step 1: Update .env File

**CRITICAL:** You need to manually edit your `.env` file (not tracked by git).

Open `capstone_backend\.env` and update these lines:

```env
# CHANGE FROM:
DB_CONNECTION=sqlite

# CHANGE TO:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=capstone_db
DB_USERNAME=root
DB_PASSWORD=
```

**Complete MySQL Configuration Block:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=capstone_db
DB_USERNAME=root
DB_PASSWORD=
```

---

## Step 2: Backend Code Changes

The following files will be automatically updated:

### A. `.env.example` 
Updated to show MySQL as default configuration.

### B. `config/database.php`
Default connection changed from `sqlite` to `mysql`.

---

## Step 3: Clear Laravel Caches

Run these commands in order:

```powershell
cd capstone_backend

# Clear configuration cache
php artisan config:clear

# Clear application cache
php artisan cache:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear
```

---

## Step 4: Test Database Connection

```powershell
# Test connection
php artisan db:show

# Should display MySQL connection info
```

---

## Step 5: Run Migrations

```powershell
# Run all migrations (creates tables)
php artisan migrate

# If you need to start fresh (WARNING: deletes all data)
php artisan migrate:fresh
```

---

## Step 6: Seed Database (Optional)

```powershell
# Run all seeders
php artisan db:seed

# Or run specific seeder
php artisan db:seed --class=UsersSeeder
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=DemoDataSeeder
```

---

## Step 7: Verify Migration

```powershell
# Check migrations status
php artisan migrate:status

# List all tables
php artisan db:table --database=mysql

# Test a query
php artisan tinker
# Then in tinker:
# User::count()
# exit
```

---

## Frontend Considerations

### ✅ NO CHANGES REQUIRED

Your React frontend (`capstone_frontend`) communicates with Laravel via HTTP API endpoints. The database change is **completely transparent** to the frontend.

**Why?**
- Frontend makes HTTP requests to Laravel API
- Laravel handles database queries internally
- API responses remain identical regardless of database type
- No frontend code changes needed

**Verify Frontend Still Works:**
```powershell
cd capstone_frontend
npm run dev
```

Test your application normally - all API calls should work identically.

---

## Common Errors & Troubleshooting

### Error: "SQLSTATE[HY000] [2002] Connection refused"

**Cause:** MySQL service is not running

**Solution:**
```powershell
# Check MySQL status
Get-Service -Name MySQL*

# Start MySQL
Start-Service -Name MySQL80

# Or restart
Restart-Service -Name MySQL80
```

---

### Error: "SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'"

**Cause:** Incorrect username/password

**Solution:**
```powershell
# Reset root password
mysql -u root

# In MySQL prompt:
ALTER USER 'root'@'localhost' IDENTIFIED BY '';
FLUSH PRIVILEGES;
EXIT;
```

Or update `.env` with correct password:
```env
DB_PASSWORD=your_actual_password
```

---

### Error: "SQLSTATE[HY000] [1049] Unknown database 'capstone_db'"

**Cause:** Database doesn't exist

**Solution:**
```powershell
mysql -u root
CREATE DATABASE capstone_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

---

### Error: "Syntax error or access violation: 1071 Specified key was too long"

**Cause:** Old MySQL version with utf8mb4 issues

**Solution:**
Add to `app/Providers/AppServiceProvider.php`:
```php
use Illuminate\Support\Facades\Schema;

public function boot()
{
    Schema::defaultStringLength(191);
}
```

---

### Error: "PDO driver not found"

**Cause:** MySQL PDO extension not installed

**Solution:**
1. Edit `php.ini`
2. Uncomment: `extension=pdo_mysql`
3. Restart PHP/web server

---

## Data Migration (If You Have Existing Data)

If you have important data in SQLite that needs to be migrated:

### Option 1: Export/Import via Laravel

```powershell
# 1. Create a backup seeder from SQLite data
php artisan db:seed --class=BackupSeeder

# 2. Switch to MySQL (follow steps above)

# 3. Run migrations and seeders
php artisan migrate
php artisan db:seed
```

### Option 2: Manual Export/Import

```powershell
# Export from SQLite
sqlite3 database/database.sqlite .dump > backup.sql

# Convert and import to MySQL (requires manual SQL editing)
# This is complex - consider using a tool like:
# - SQLite to MySQL converter tools
# - phpMyAdmin import
```

---

## Verification Checklist

- [ ] MySQL service is running
- [ ] Database `capstone_db` exists
- [ ] `.env` file updated with MySQL credentials
- [ ] `php artisan config:clear` executed
- [ ] `php artisan migrate` completed successfully
- [ ] `php artisan db:seed` completed (if needed)
- [ ] `php artisan db:show` shows MySQL connection
- [ ] Backend API endpoints work correctly
- [ ] Frontend can communicate with backend
- [ ] All CRUD operations function properly

---

## Quick Command Reference

```powershell
# Complete migration in one go (after .env is updated):
cd capstone_backend
php artisan config:clear
php artisan cache:clear
php artisan migrate:fresh --seed
php artisan db:show

# Start development server
php artisan serve

# In another terminal, start frontend
cd capstone_frontend
npm run dev
```

---

## Rollback to SQLite (If Needed)

If you need to revert:

1. Update `.env`:
   ```env
   DB_CONNECTION=sqlite
   ```

2. Clear caches:
   ```powershell
   php artisan config:clear
   php artisan cache:clear
   ```

3. Ensure `database/database.sqlite` exists:
   ```powershell
   New-Item -Path database/database.sqlite -ItemType File -Force
   ```

4. Run migrations:
   ```powershell
   php artisan migrate:fresh --seed
   ```

---

## Performance Considerations

**MySQL vs SQLite:**
- ✅ Better for concurrent users
- ✅ Better for production environments
- ✅ More robust transaction handling
- ✅ Better indexing capabilities
- ⚠️ Requires separate service running
- ⚠️ More complex setup

---

## Next Steps After Migration

1. **Update Production Environment**
   - Configure production MySQL server
   - Update production `.env` file
   - Run migrations on production

2. **Backup Strategy**
   ```powershell
   # Regular MySQL backups
   mysqldump -u root capstone_db > backup_$(Get-Date -Format 'yyyyMMdd').sql
   ```

3. **Monitoring**
   - Monitor MySQL performance
   - Check slow query log
   - Optimize indexes if needed

---

## Support

If you encounter issues not covered here:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check MySQL error log
3. Run `php artisan db:show` for connection details
4. Verify `.env` file has no syntax errors
