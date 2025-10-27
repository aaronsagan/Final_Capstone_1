# Fix "Invalid Credentials" Error - COMPLETE SOLUTION

## 🔴 PROBLEM: "Invalid credentials" error when logging in

**Possible Causes:**
1. Users don't exist in database
2. Password hash doesn't match
3. User status is 'suspended'
4. Email doesn't match exactly

---

## ✅ SOLUTION: Use New Artisan Command

I created a custom command that will properly create all demo users with correct password hashing.

### 🚀 RUN THIS COMMAND:

```bash
cd capstone_backend
php artisan demo:users
```

**This will:**
- ✅ Create/update admin@example.com
- ✅ Create/update donor@example.com
- ✅ Create/update charityadmin@example.com
- ✅ Set password to 'password' with proper hashing
- ✅ Set all statuses to 'active'
- ✅ Create charity for charity admin

---

## 🧪 TEST IMMEDIATELY AFTER:

### Test 1: Admin Login
```
Email: admin@example.com
Password: password
```

### Test 2: Donor Login
```
Email: donor@example.com
Password: password
```

### Test 3: Charity Login
```
Email: charityadmin@example.com
Password: password
```

---

## 🔍 IF STILL "Invalid credentials"

### Debug Step 1: Check if users exist

```bash
php artisan tinker

# Run these commands:
User::where('email', 'admin@example.com')->first()
User::where('email', 'donor@example.com')->first()
User::where('email', 'charityadmin@example.com')->first()

# Each should return a user object, not null
```

**If any return null:**
```bash
php artisan demo:users
```

---

### Debug Step 2: Check password hash

```bash
php artisan tinker

# Test password verification:
$user = User::where('email', 'donor@example.com')->first();
Hash::check('password', $user->password);

# Should return: true
```

**If returns false:**
```bash
# The password hash is wrong, recreate user:
php artisan demo:users
```

---

### Debug Step 3: Check user status

```bash
php artisan tinker

# Check status:
User::where('email', 'donor@example.com')->value('status');

# Should return: "active"
```

**If returns "suspended":**
```bash
php artisan tinker

# Fix it:
User::where('email', 'donor@example.com')->update(['status' => 'active']);
```

---

### Debug Step 4: Test login API directly

```bash
# Test with curl:
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"donor@example.com","password":"password"}'

# Expected 200 response:
# {
#   "token": "1|xxxxx",
#   "user": {
#     "id": 2,
#     "name": "Demo Donor",
#     "email": "donor@example.com",
#     "role": "donor"
#   }
# }

# If 401 response:
# {"message":"Invalid credentials"}
```

**If 401, the user doesn't exist or password is wrong:**
```bash
php artisan demo:users
```

---

## 🔧 ALTERNATIVE: Manual Database Fix

If the artisan command doesn't work, manually insert users:

```sql
-- Delete existing users (optional)
DELETE FROM users WHERE email IN ('admin@example.com', 'donor@example.com', 'charityadmin@example.com');

-- Insert System Admin
INSERT INTO users (name, email, password, role, status, phone, created_at, updated_at)
VALUES (
    'System Admin',
    'admin@example.com',
    '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANh6Vgg.iyC', -- password: 'password'
    'admin',
    'active',
    '09171111111',
    NOW(),
    NOW()
);

-- Insert Demo Donor
INSERT INTO users (name, email, password, role, status, phone, address, created_at, updated_at)
VALUES (
    'Demo Donor',
    'donor@example.com',
    '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANh6Vgg.iyC', -- password: 'password'
    'donor',
    'active',
    '09172222222',
    '123 Donor Street, Manila',
    NOW(),
    NOW()
);

-- Insert Charity Admin
INSERT INTO users (name, email, password, role, status, phone, created_at, updated_at)
VALUES (
    'Charity Admin',
    'charityadmin@example.com',
    '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANh6Vgg.iyC', -- password: 'password'
    'charity_admin',
    'active',
    '09173333333',
    NOW(),
    NOW()
);

-- Verify
SELECT id, name, email, role, status FROM users;
```

---

## 🎯 COMPLETE FIX PROCEDURE

### Step 1: Run the command
```bash
cd capstone_backend
php artisan demo:users
```

### Step 2: Verify users created
```bash
php artisan tinker

User::count()  # Should be at least 3
User::pluck('email')  # Should show all 3 emails
```

### Step 3: Test password hash
```bash
php artisan tinker

$user = User::where('email', 'donor@example.com')->first();
Hash::check('password', $user->password)  # Should return true
```

### Step 4: Test login via curl
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"donor@example.com","password":"password"}'

# Should return 200 with token
```

### Step 5: Test in browser
```
1. Go to http://localhost:8080/auth/login
2. Enter: donor@example.com / password
3. Should login successfully
```

---

## 📊 VERIFY EVERYTHING

### Check Users Table:
```sql
SELECT 
    id, 
    name, 
    email, 
    role, 
    status,
    LENGTH(password) as password_length
FROM users
WHERE email IN ('admin@example.com', 'donor@example.com', 'charityadmin@example.com');

-- Expected:
-- All 3 users exist
-- All have status = 'active'
-- All have password_length = 60 (bcrypt hash)
```

### Test Each Password:
```bash
php artisan tinker

# Test admin
$admin = User::where('email', 'admin@example.com')->first();
Hash::check('password', $admin->password);  # true

# Test donor
$donor = User::where('email', 'donor@example.com')->first();
Hash::check('password', $donor->password);  # true

# Test charity
$charity = User::where('email', 'charityadmin@example.com')->first();
Hash::check('password', $charity->password);  # true
```

---

## 🆘 NUCLEAR OPTION: Complete Reset

If nothing works, do a complete database reset:

```bash
cd capstone_backend

# 1. Drop all tables
php artisan migrate:fresh

# 2. Create demo users
php artisan demo:users

# 3. Verify
php artisan tinker
User::count()  # Should be 3

# 4. Test login
exit
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"donor@example.com","password":"password"}'
```

---

## ✅ SUCCESS CHECKLIST

After running `php artisan demo:users`, verify:

- [ ] Command shows "✓ Created: admin@example.com"
- [ ] Command shows "✓ Created: donor@example.com"
- [ ] Command shows "✓ Created: charityadmin@example.com"
- [ ] `User::count()` returns at least 3
- [ ] Password hash check returns true
- [ ] curl login test returns 200
- [ ] Browser login works for all 3 accounts

---

## 🔑 DEMO ACCOUNTS (After Fix)

| Email | Password | Role | Status |
|-------|----------|------|--------|
| admin@example.com | password | admin | active |
| donor@example.com | password | donor | active |
| charityadmin@example.com | password | charity_admin | active |

---

## 📝 COMMON ISSUES & FIXES

### Issue: "User not found"
```bash
php artisan demo:users
```

### Issue: "Password doesn't match"
```bash
php artisan demo:users
```

### Issue: "User suspended"
```bash
php artisan tinker
User::where('email', 'donor@example.com')->update(['status' => 'active']);
```

### Issue: "SQLSTATE[23000]: Integrity constraint violation"
```bash
# Delete existing users first
php artisan tinker
User::where('email', 'donor@example.com')->delete();
exit

# Then recreate
php artisan demo:users
```

---

## 🚀 FINAL COMMAND TO RUN:

```bash
cd capstone_backend
php artisan demo:users
```

**Then test login with:**
- Email: `donor@example.com`
- Password: `password`

**Should work now!** ✅
