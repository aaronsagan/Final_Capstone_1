# 🚀 Run These Commands to Fix Registration

## The Problem
The database doesn't have the new primary contact columns yet, so when the backend tries to save data, it fails with a 500 error.

## The Solution
Run the migration to add the new columns to the database.

---

## Commands to Run

### 1. Open Terminal in Backend Directory
```bash
cd c:\Users\sagan\Capstone\capstone_backend
```

### 2. Run the Migration
```bash
php artisan migrate
```

**Expected Output:**
```
Migrating: 2025_10_20_000002_update_charities_primary_contact_fields
Migrated:  2025_10_20_000002_update_charities_primary_contact_fields (XX.XXms)
```

### 3. Verify Database (Optional)
Check that the new columns exist:
```bash
php artisan tinker
```

Then in tinker:
```php
DB::select("DESCRIBE charities");
exit
```

You should see these new columns:
- `primary_first_name`
- `primary_middle_initial`
- `primary_last_name`
- `primary_position`
- `primary_email`
- `primary_phone`

---

## After Running Migration

1. Go back to your frontend: `http://localhost:8080/register-charity`
2. Fill out the charity registration form
3. Submit it
4. You should now see: **"Registration successful!"** ✅

---

## If Migration Fails

### Error: "Column already exists"
This means you already ran the migration. Skip to testing.

### Error: "Table not found"
Run this first:
```bash
php artisan migrate:fresh
php artisan db:seed
```
**⚠️ WARNING: This will delete all data!**

### Error: "Connection refused"
Make sure your MySQL server is running and check your `.env` file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=capstone_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

---

## Quick Test

After migration, test with these steps:

1. **Fill out the form:**
   - Organization Name: Test Charity
   - First Name: Juan
   - Last Name: Dela Cruz
   - Email: test@charity.org
   - Phone: 09171234567
   - Password: Test@123
   - (Fill other required fields)

2. **Submit**

3. **Expected Result:**
   - ✅ "Registration successful!"
   - Redirects to login page

4. **Check Database:**
```sql
SELECT name, primary_first_name, primary_last_name, primary_email 
FROM charities 
ORDER BY id DESC 
LIMIT 1;
```

---

## Still Getting Errors?

Check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

This will show you the exact error message.

---

## Summary

**Just run this:**
```bash
cd c:\Users\sagan\Capstone\capstone_backend
php artisan migrate
```

Then try registering again! 🎉
