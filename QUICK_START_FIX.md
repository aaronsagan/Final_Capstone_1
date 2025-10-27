# Quick Start - Fix Login & Registration

## 🚀 IMMEDIATE ACTIONS

### 1. Run Migrations (CRITICAL)
```bash
cd capstone_backend
php artisan migrate
```

### 2. Clear All Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 3. Link Storage
```bash
php artisan storage:link
```

### 4. Restart Backend Server
```bash
# Stop current server (Ctrl+C)
php artisan serve
```

### 5. Test API
Open browser: `http://localhost:8000/api/ping`

Should see: `{"ok":true,"time":"..."}`

---

## ✅ WHAT WAS FIXED

1. ✅ **CORS Middleware Added**
   - Created `app/Http/Middleware/Cors.php`
   - Registered in `bootstrap/app.php`
   - Fixes "CORS policy" errors

2. ✅ **Database Seeder Fixed**
   - Fixed `DemoDataSeeder.php`
   - Now creates user if doesn't exist
   - Fixes "Attempt to read property id on null" error

3. ✅ **All Migrations Created**
   - cover_image column
   - Missing charity fields
   - Document types updated

4. ✅ **Charity Model Updated**
   - All fields in fillable array

5. ✅ **AuthController Updated**
   - Database transaction added
   - All fields saved correctly

---

## 🧪 TEST NOW

### Test 1: Register Donor
```
1. Go to: http://localhost:8080/auth/register/donor
2. Fill form:
   - Full name: John Doe
   - Email: john@test.com
   - Password: password123
   - Confirm: password123
3. Click "Create account"
4. ✅ Should redirect to login
```

### Test 2: Login
```
1. Go to: http://localhost:8080/auth/login
2. Enter:
   - Email: john@test.com
   - Password: password123
3. Click "Login"
4. ✅ Should redirect to /donor dashboard
```

### Test 3: Register Charity
```
1. Go to: http://localhost:8080/auth/register/charity
2. Fill all 4 steps
3. Upload files (optional)
4. Click "Submit Application"
5. ✅ Should redirect to login
6. Login with:
   - Email: (your contact_email)
   - Password: TempPassword123!
7. ✅ Should redirect to /charity dashboard
```

---

## 🔍 IF STILL GETTING 422 ERROR

### Check Browser Console (F12):
1. Open DevTools
2. Go to Network tab
3. Find the failed request
4. Click on it
5. Look at "Response" tab
6. See what validation error it shows

### Common 422 Causes:

**"The email has already been taken"**
- Solution: Use a different email

**"The password confirmation does not match"**
- Solution: Make sure password and confirm password are identical

**"The name field is required"**
- Solution: Fill all required fields

---

## 🔍 IF LOGIN NOT WORKING

### Check:
1. **Backend running?**
   ```bash
   # Should see server running
   php artisan serve
   ```

2. **User exists in database?**
   ```sql
   SELECT * FROM users WHERE email = 'your@email.com';
   ```

3. **Password correct?**
   - Try registering a new account
   - Use that to test login

4. **Browser console errors?**
   - F12 → Console tab
   - Look for errors

5. **Network tab shows request?**
   - F12 → Network tab
   - See if POST to /api/auth/login happens
   - Check response

---

## 📊 VERIFY EVERYTHING WORKS

### Backend Health Check:
```bash
# Test ping
curl http://localhost:8000/api/ping

# Should return:
# {"ok":true,"time":"2025-10-02 15:12:00"}

# Test metrics
curl http://localhost:8000/api/metrics

# Should return:
# {"total_users":0,"total_donors":0,...}
```

### Database Check:
```sql
-- Check tables exist
SHOW TABLES;

-- Should see:
-- users
-- charities
-- charity_documents
-- campaigns
-- donations
-- etc.

-- Check users table structure
DESCRIBE users;

-- Should have:
-- id, name, email, password, phone, address, profile_image, role, status
```

---

## 🎯 CHECKLIST

Before testing:
- [ ] Ran `php artisan migrate`
- [ ] Ran `php artisan storage:link`
- [ ] Cleared caches
- [ ] Backend server running on port 8000
- [ ] Frontend server running on port 8080
- [ ] Can access http://localhost:8000/api/ping

Test registration:
- [ ] Can register donor
- [ ] Can register charity
- [ ] No 422 errors
- [ ] Redirects to login

Test login:
- [ ] Can login with donor account
- [ ] Can login with charity account
- [ ] Redirects to correct dashboard
- [ ] No errors in console

---

## 🆘 EMERGENCY RESET

If nothing works, do a complete reset:

```bash
cd capstone_backend

# 1. Drop all tables and recreate
php artisan migrate:fresh

# 2. Seed demo data
php artisan db:seed --class=DemoDataSeeder

# 3. Clear everything
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 4. Link storage
php artisan storage:link

# 5. Fix permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# 6. Restart server
php artisan serve
```

Then test with demo accounts:
- Email: `charityadmin@example.com`
- Password: `password`

---

## ✅ SUCCESS!

If you can:
- ✅ Register a donor account
- ✅ Login with that account
- ✅ See the donor dashboard
- ✅ Register a charity account
- ✅ Login with charity account
- ✅ See the charity dashboard

**Then everything is working!** 🎉

---

## 📝 NOTES

- Default charity password: `TempPassword123!`
- Demo account password: `password`
- API runs on: `http://localhost:8000`
- Frontend runs on: `http://localhost:8080`

---

**Run the commands above and test again!**
