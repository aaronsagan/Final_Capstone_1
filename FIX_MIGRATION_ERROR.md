# Fix Migration Error

## The Problem
The migration failed because it tried to copy data from old columns that don't exist in your database.

## The Solution
I've fixed the migration to check if old columns exist before trying to migrate data.

---

## Run These Commands

### 1. Rollback the Failed Migration
```bash
cd c:\Users\sagan\Capstone\capstone_backend
php artisan migrate:rollback --step=1
```

### 2. Run the Migration Again
```bash
php artisan migrate
```

**Expected Output:**
```
INFO  Running migrations.

2025_10_20_000002_update_charities_primary_contact_fields ........... XX.XXms DONE
```

---

## Verify It Worked

Check that the columns were added:
```bash
php artisan tinker
```

Then:
```php
Schema::hasColumn('charities', 'primary_first_name')
// Should return: true

Schema::hasColumn('charities', 'primary_email')
// Should return: true

exit
```

---

## Now Test Registration

1. Go to: `http://localhost:8080/register-charity`
2. Fill out the form
3. Submit
4. You should see: **"Registration successful!"** ✅

---

## What I Fixed

The migration now checks if old columns exist before trying to migrate data:

```php
if (Schema::hasColumn('charities', 'contact_person_name')) {
    // Only migrate if old columns exist
    DB::statement("UPDATE charities SET ...");
}
```

This way, it works whether you have old data or not!
