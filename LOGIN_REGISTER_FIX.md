# Login & Registration Fix - Complete Guide

## 🔧 ISSUES TO FIX

### Issue 1: Registration 422 Error
**Cause:** Password confirmation field mismatch or validation failure

### Issue 2: Login Not Working
**Cause:** Possible CORS, API URL, or authentication issues

---

## ✅ STEP-BY-STEP FIX

### Step 1: Verify Database Migrations

```bash
cd capstone_backend

# Run all migrations
php artisan migrate

# Expected migrations:
# - create_users_table
# - create_charities_table
# - create_charity_documents_table
# - add_donor_fields_to_users_table
# - add_cover_image_to_charities_table
# - add_missing_fields_to_charities_table
# - update_charity_documents_doc_types
```

---

### Step 2: Check API Configuration

**Frontend `.env.local`:**
```env
VITE_API_URL=http://localhost:8000
```

**Backend `.env`:**
```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

---

### Step 3: Enable CORS

**Create/Update:** `capstone_backend/bootstrap/app.php`

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Add CORS middleware
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

**Add CORS headers to API responses:**

Create `capstone_backend/app/Http/Middleware/Cors.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
    }
}
```

**Register in:** `capstone_backend/bootstrap/app.php`

---

### Step 4: Test API Endpoints

```bash
# Test ping
curl http://localhost:8000/api/ping

# Expected: {"ok":true,"time":"2025-10-02 15:12:00"}

# Test metrics
curl http://localhost:8000/api/metrics

# Expected: {"total_users":0,"total_donors":0,...}
```

---

### Step 5: Test Registration

**Test Donor Registration:**

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -F "name=Test Donor" \
  -F "email=donor@test.com" \
  -F "password=password123" \
  -F "password_confirmation=password123" \
  -F "phone=09171234567"

# Expected 201:
# {
#   "message": "Registration successful",
#   "user": {
#     "id": 1,
#     "name": "Test Donor",
#     "email": "donor@test.com",
#     "role": "donor",
#     "status": "active"
#   }
# }

# If 422, check error response for specific validation issues
```

**Test Login:**

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"donor@test.com","password":"password123"}'

# Expected 200:
# {
#   "token": "1|xxxxxxxxxxxxx",
#   "user": {
#     "id": 1,
#     "name": "Test Donor",
#     "email": "donor@test.com",
#     "role": "donor"
#   }
# }
```

---

## 🔍 COMMON ISSUES & SOLUTIONS

### Issue: "SQLSTATE[42S02]: Base table or view not found"
**Solution:**
```bash
php artisan migrate:fresh
php artisan db:seed --class=DemoDataSeeder
```

### Issue: "CORS policy: No 'Access-Control-Allow-Origin' header"
**Solution:** Add CORS middleware (see Step 3)

### Issue: "422 Unprocessable Entity"
**Check:**
1. Password confirmation matches
2. Email is unique
3. All required fields present
4. Check Laravel logs: `storage/logs/laravel.log`

### Issue: "500 Internal Server Error"
**Check:**
1. Database connection
2. Laravel logs
3. File permissions: `chmod -R 775 storage bootstrap/cache`

### Issue: "Network Error" or "Failed to fetch"
**Check:**
1. Backend server running: `php artisan serve`
2. Frontend API URL correct
3. Port not blocked by firewall

---

## 🧪 COMPLETE TESTING PROCEDURE

### Test 1: Backend Server Running

```bash
cd capstone_backend
php artisan serve

# Should show:
# Server running on [http://127.0.0.1:8000]
```

### Test 2: Database Connection

```bash
php artisan migrate:status

# Should show all migrations with status
```

### Test 3: API Accessible

Open browser: `http://localhost:8000/api/ping`

Should see: `{"ok":true,"time":"..."}`

### Test 4: Frontend API Connection

Check browser console (F12) when on frontend:
- No CORS errors
- API calls going to correct URL
- Check Network tab for request/response

### Test 5: Register Donor

1. Go to: `http://localhost:8080/auth/register/donor`
2. Fill form:
   - Full name: "John Doe"
   - Email: "john@test.com"
   - Password: "password123"
   - Confirm: "password123"
3. Submit
4. Check browser console for errors
5. Check Network tab for API response

**If 422:**
- Check response body for validation errors
- Check Laravel logs
- Verify password confirmation matches

### Test 6: Login

1. Go to: `http://localhost:8080/auth/login`
2. Enter:
   - Email: "john@test.com"
   - Password: "password123"
3. Submit
4. Should redirect to `/donor` dashboard

**If fails:**
- Check browser console
- Check Network tab
- Verify user exists in database
- Check Laravel logs

---

## 🔧 QUICK FIX COMMANDS

```bash
# 1. Clear all caches
cd capstone_backend
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 2. Regenerate key
php artisan key:generate

# 3. Fresh migration
php artisan migrate:fresh

# 4. Seed demo data
php artisan db:seed --class=DemoDataSeeder

# 5. Fix permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# 6. Link storage
php artisan storage:link

# 7. Restart server
php artisan serve
```

---

## 📊 VERIFY DATABASE

```sql
-- Check users table exists
SHOW TABLES LIKE 'users';

-- Check users table structure
DESCRIBE users;

-- Should have columns:
-- id, name, email, password, phone, address, profile_image, role, status, created_at, updated_at

-- Check if any users exist
SELECT * FROM users;

-- Check charities table
DESCRIBE charities;

-- Should have columns including:
-- id, owner_id, name, legal_trading_name, address, region, municipality, category, logo_path, cover_image
```

---

## 🚀 FINAL CHECKLIST

Before testing:
- [ ] Backend server running (`php artisan serve`)
- [ ] Frontend server running (`npm run dev`)
- [ ] Database migrations run
- [ ] Storage linked
- [ ] Permissions correct
- [ ] API URL configured
- [ ] CORS enabled

Test registration:
- [ ] Donor registration works
- [ ] Charity registration works
- [ ] No 422 errors
- [ ] No 500 errors
- [ ] Redirects to login

Test login:
- [ ] Login with donor account
- [ ] Login with charity account
- [ ] Redirects to correct dashboard
- [ ] Token saved
- [ ] User data loaded

---

## 🔐 DEFAULT ACCOUNTS (After Seeding)

**Charity Admin:**
- Email: `charityadmin@example.com`
- Password: `password`

**System Admin:**
- Email: `admin@example.com`
- Password: `password`

---

## 📝 DEBUGGING TIPS

### Enable Debug Mode

**Backend `.env`:**
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Check Logs

```bash
# Watch Laravel logs
tail -f storage/logs/laravel.log

# Clear logs
> storage/logs/laravel.log
```

### Browser Console

1. Open DevTools (F12)
2. Go to Console tab
3. Look for errors
4. Go to Network tab
5. Check API requests/responses

### Test with Postman/Insomnia

1. Import API endpoints
2. Test each endpoint individually
3. Check request/response
4. Verify headers

---

## ✅ SUCCESS CRITERIA

Registration should:
- ✅ Return 201 status
- ✅ Create user in database
- ✅ Return user object
- ✅ Redirect to login
- ✅ No errors in console

Login should:
- ✅ Return 200 status
- ✅ Return token
- ✅ Return user object
- ✅ Save token to storage
- ✅ Redirect to dashboard
- ✅ Load user data

---

## 🆘 IF STILL NOT WORKING

1. **Check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Check browser console:**
   - F12 → Console tab
   - Look for errors

3. **Check Network tab:**
   - See actual request/response
   - Check status codes
   - Check response body

4. **Verify database:**
   ```sql
   SELECT * FROM users;
   SELECT * FROM charities;
   ```

5. **Test API directly:**
   ```bash
   curl -X POST http://localhost:8000/api/auth/register \
     -F "name=Test" \
     -F "email=test@test.com" \
     -F "password=password" \
     -F "password_confirmation=password"
   ```

6. **Check server is running:**
   ```bash
   ps aux | grep "php artisan serve"
   ```

7. **Restart everything:**
   ```bash
   # Kill servers
   # Restart backend
   php artisan serve
   # Restart frontend
   npm run dev
   ```

---

## 🎯 MOST LIKELY ISSUES

1. **Migrations not run** → Run `php artisan migrate`
2. **CORS not enabled** → Add CORS middleware
3. **Wrong API URL** → Check `.env.local`
4. **Server not running** → Start with `php artisan serve`
5. **Password mismatch** → Check password_confirmation field
6. **Database not connected** → Check `.env` database settings

---

Run through this checklist and the issues should be resolved!
