# ✅ Demo Users Created Successfully!

## 🎉 ALL ACCOUNTS ARE NOW READY

The command ran successfully and created all demo users with proper password hashing.

---

## 🔑 LOGIN CREDENTIALS

### System Admin
```
Email: admin@example.com
Password: password
Dashboard: /admin
```

### Demo Donor
```
Email: donor@example.com
Password: password
Dashboard: /donor
```

### Charity Admin
```
Email: charityadmin@example.com
Password: password
Dashboard: /charity
```

---

## 🧪 TEST NOW

### Step 1: Test Donor Login

1. Go to: `http://localhost:8080/auth/login`
2. Enter:
   - Email: `donor@example.com`
   - Password: `password`
3. Click "Login"
4. ✅ Should redirect to `/donor` dashboard

### Step 2: Test Admin Login

1. Go to: `http://localhost:8080/auth/login`
2. Enter:
   - Email: `admin@example.com`
   - Password: `password`
3. Click "Login"
4. ✅ Should redirect to `/admin` dashboard

### Step 3: Test Charity Login

1. Go to: `http://localhost:8080/auth/login`
2. Enter:
   - Email: `charityadmin@example.com`
   - Password: `password`
3. Click "Login"
4. ✅ Should redirect to `/charity` dashboard

---

## 🔍 IF STILL "Invalid credentials"

### Check 1: Backend Server Running?

```bash
# Make sure backend is running
php artisan serve

# Should show:
# Server running on [http://127.0.0.1:8000]
```

### Check 2: Test API Directly

```bash
# Test login API
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"donor@example.com\",\"password\":\"password\"}"

# Expected response (200):
# {
#   "token": "1|xxxxx",
#   "user": {
#     "id": 2,
#     "name": "Demo Donor",
#     "email": "donor@example.com",
#     "role": "donor"
#   }
# }
```

### Check 3: Verify Users in Database

```bash
php artisan tinker

# Check users exist:
User::all()->pluck('email');

# Should show:
# [
#   "admin@example.com",
#   "donor@example.com",
#   "charityadmin@example.com"
# ]

# Test password:
$user = User::where('email', 'donor@example.com')->first();
Hash::check('password', $user->password);

# Should return: true
```

### Check 4: Browser Console

1. Open browser DevTools (F12)
2. Go to Console tab
3. Look for errors
4. Go to Network tab
5. Try to login
6. Check the login request:
   - URL should be: `http://localhost:8000/api/auth/login`
   - Method: POST
   - Status: Should be 200 (not 401)
   - Response: Should have token and user

---

## 🔧 COMMON ISSUES

### Issue 1: "Network Error" or "Failed to fetch"

**Cause:** Backend not running or wrong API URL

**Fix:**
```bash
# Start backend
cd capstone_backend
php artisan serve

# Check frontend .env.local
# Should have: VITE_API_URL=http://localhost:8000
```

### Issue 2: "CORS Error"

**Cause:** CORS middleware not working

**Fix:** Already fixed in bootstrap/app.php, restart backend:
```bash
# Stop server (Ctrl+C)
php artisan serve
```

### Issue 3: Still "Invalid credentials" with correct password

**Cause:** Frontend sending wrong data format

**Check:** Browser DevTools → Network → Login request → Payload

Should be:
```json
{
  "email": "donor@example.com",
  "password": "password"
}
```

---

## 📊 VERIFY EVERYTHING WORKS

### Test 1: API Health Check
```bash
curl http://localhost:8000/api/ping

# Expected:
# {"ok":true,"time":"2025-10-02 15:25:00"}
```

### Test 2: API Login Test
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"donor@example.com\",\"password\":\"password\"}"

# Expected: 200 with token
```

### Test 3: Browser Login Test
1. Open `http://localhost:8080/auth/login`
2. Enter credentials
3. Check Network tab for response
4. Should get token and redirect

---

## ✅ SUCCESS CRITERIA

After testing, you should:
- ✅ Login as donor → See donor dashboard
- ✅ Login as admin → See admin dashboard
- ✅ Login as charity → See charity dashboard
- ✅ No "Invalid credentials" error
- ✅ No CORS errors
- ✅ Proper redirects

---

## 🆘 EMERGENCY TROUBLESHOOTING

### If API returns 401:

```bash
# Recreate users
php artisan demo:users

# Test immediately
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"donor@example.com\",\"password\":\"password\"}"
```

### If frontend can't connect:

1. Check backend running: `php artisan serve`
2. Check frontend .env.local: `VITE_API_URL=http://localhost:8000`
3. Restart frontend: Stop and run `npm run dev`
4. Clear browser cache: Ctrl+Shift+Delete

### If password doesn't work:

```bash
# Reset password manually
php artisan tinker

$user = User::where('email', 'donor@example.com')->first();
$user->password = Hash::make('password');
$user->save();

# Test
Hash::check('password', $user->password);  # Should be true
```

---

## 🎯 QUICK TEST COMMANDS

```bash
# 1. Check backend running
curl http://localhost:8000/api/ping

# 2. Test login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"donor@example.com\",\"password\":\"password\"}"

# 3. Check users exist
php artisan tinker
User::count()  # Should be 3
```

---

## ✅ FINAL CHECKLIST

Before testing login in browser:
- [x] Migrations ran successfully ✅
- [x] Demo users created ✅
- [ ] Backend server running (php artisan serve)
- [ ] Frontend server running (npm run dev)
- [ ] API ping works
- [ ] API login test works

Test in browser:
- [ ] Can login as donor@example.com
- [ ] Can login as admin@example.com
- [ ] Can login as charityadmin@example.com
- [ ] Redirects to correct dashboard
- [ ] No errors in console

---

**Try logging in now with:**
- Email: `donor@example.com`
- Password: `password`

**It should work!** 🚀
