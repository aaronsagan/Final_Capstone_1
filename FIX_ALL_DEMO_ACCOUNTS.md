# Fix All Demo Accounts - Complete Guide

## 🔧 ISSUE: Only charityadmin@example.com works

**Problem:** Only charity admin account exists in database. Donor and admin accounts missing.

**Solution:** Updated seeder to create all 3 demo accounts.

---

## ✅ WHAT WAS FIXED

### Updated DemoDataSeeder.php

**Now Creates 3 Accounts:**

1. **System Admin**
   - Email: `admin@example.com`
   - Password: `password`
   - Role: `admin`

2. **Demo Donor**
   - Email: `donor@example.com`
   - Password: `password`
   - Role: `donor`

3. **Charity Admin**
   - Email: `charityadmin@example.com`
   - Password: `password`
   - Role: `charity_admin`

---

## 🚀 RUN THIS NOW TO FIX:

### Option 1: Re-run Seeder (Recommended)

```bash
cd capstone_backend

# Run the seeder again
php artisan db:seed --class=DemoDataSeeder
```

This will create the missing accounts without affecting existing data.

---

### Option 2: Fresh Start (If you want clean database)

```bash
cd capstone_backend

# Drop all tables and recreate
php artisan migrate:fresh

# Seed demo data
php artisan db:seed --class=DemoDataSeeder
```

⚠️ **Warning:** This will delete ALL existing data!

---

## 🧪 TEST ALL ACCOUNTS

### Test 1: System Admin Login

```
URL: http://localhost:8080/auth/login

Credentials:
- Email: admin@example.com
- Password: password

Expected:
✅ Login successful
✅ Redirect to /admin dashboard
✅ See admin panel with metrics
```

### Test 2: Donor Login

```
URL: http://localhost:8080/auth/login

Credentials:
- Email: donor@example.com
- Password: password

Expected:
✅ Login successful
✅ Redirect to /donor dashboard
✅ See donor dashboard
```

### Test 3: Charity Admin Login

```
URL: http://localhost:8080/auth/login

Credentials:
- Email: charityadmin@example.com
- Password: password

Expected:
✅ Login successful
✅ Redirect to /charity dashboard
✅ See charity dashboard
```

---

## 📊 VERIFY IN DATABASE

```sql
-- Check all users
SELECT id, name, email, role, status FROM users;

-- Expected output:
-- +----+---------------+---------------------------+---------------+--------+
-- | id | name          | email                     | role          | status |
-- +----+---------------+---------------------------+---------------+--------+
-- |  1 | System Admin  | admin@example.com         | admin         | active |
-- |  2 | Demo Donor    | donor@example.com         | donor         | active |
-- |  3 | Charity Admin | charityadmin@example.com  | charity_admin | active |
-- +----+---------------+---------------------------+---------------+--------+

-- Check charity
SELECT id, name, owner_id, verification_status FROM charities;

-- Expected output:
-- +----+---------------------+----------+---------------------+
-- | id | name                | owner_id | verification_status |
-- +----+---------------------+----------+---------------------+
-- |  1 | HopeWorks Foundation|    3     | approved            |
-- +----+---------------------+----------+---------------------+
```

---

## 🔍 IF STILL NOT WORKING

### Check 1: Users Exist in Database

```bash
# Connect to MySQL
mysql -u your_username -p

# Select database
USE your_database_name;

# Check users
SELECT * FROM users;
```

**If no users or missing users:**
```bash
php artisan db:seed --class=DemoDataSeeder
```

---

### Check 2: Password Hash Correct

```bash
# Test password hash
php artisan tinker

# In tinker:
$user = App\Models\User::where('email', 'donor@example.com')->first();
Hash::check('password', $user->password);

# Should return: true
```

**If returns false or user not found:**
```bash
php artisan db:seed --class=DemoDataSeeder
```

---

### Check 3: User Status is Active

```sql
SELECT email, status FROM users;

-- All should be 'active'
-- If any are 'suspended', update:
UPDATE users SET status = 'active' WHERE email = 'donor@example.com';
```

---

### Check 4: Backend Login Logic

Test login directly with curl:

```bash
# Test admin login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Expected 200:
# {"token":"1|xxxxx","user":{"id":1,"name":"System Admin","email":"admin@example.com","role":"admin"}}

# Test donor login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"donor@example.com","password":"password"}'

# Expected 200:
# {"token":"2|xxxxx","user":{"id":2,"name":"Demo Donor","email":"donor@example.com","role":"donor"}}
```

**If 401 Unauthorized:**
- User doesn't exist → Run seeder
- Wrong password → Check password hash
- User suspended → Update status to 'active'

---

## 🔧 MANUAL FIX (If seeder doesn't work)

### Create Users Manually in Database:

```sql
-- Create System Admin
INSERT INTO users (name, email, password, role, status, phone, created_at, updated_at)
VALUES (
    'System Admin',
    'admin@example.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: 'password'
    'admin',
    'active',
    '09171111111',
    NOW(),
    NOW()
);

-- Create Demo Donor
INSERT INTO users (name, email, password, role, status, phone, address, created_at, updated_at)
VALUES (
    'Demo Donor',
    'donor@example.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: 'password'
    'donor',
    'active',
    '09172222222',
    '123 Donor Street, Manila',
    NOW(),
    NOW()
);

-- Verify
SELECT id, name, email, role FROM users;
```

---

## 📋 COMPLETE DEMO ACCOUNTS LIST

| Email | Password | Role | Dashboard |
|-------|----------|------|-----------|
| `admin@example.com` | `password` | admin | `/admin` |
| `donor@example.com` | `password` | donor | `/donor` |
| `charityadmin@example.com` | `password` | charity_admin | `/charity` |

---

## 🎯 STEP-BY-STEP FIX PROCEDURE

### Step 1: Run Seeder
```bash
cd capstone_backend
php artisan db:seed --class=DemoDataSeeder
```

### Step 2: Verify Users Created
```bash
php artisan tinker

# In tinker:
User::all()->pluck('email');

# Should show:
# => Illuminate\Support\Collection {
#      all: [
#        "admin@example.com",
#        "donor@example.com",
#        "charityadmin@example.com",
#      ],
#    }
```

### Step 3: Test Each Login
```bash
# Test admin
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Test donor
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"donor@example.com","password":"password"}'

# Test charity
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"charityadmin@example.com","password":"password"}'
```

### Step 4: Test in Browser
1. Go to `http://localhost:8080/auth/login`
2. Try each account
3. Verify redirects to correct dashboard

---

## ✅ SUCCESS CRITERIA

After running the seeder, you should be able to:

- ✅ Login as `admin@example.com` → See admin dashboard
- ✅ Login as `donor@example.com` → See donor dashboard
- ✅ Login as `charityadmin@example.com` → See charity dashboard
- ✅ All accounts have password: `password`
- ✅ All accounts have status: `active`
- ✅ No 401 errors
- ✅ Proper redirects

---

## 🆘 EMERGENCY RESET

If nothing works, do a complete reset:

```bash
cd capstone_backend

# 1. Fresh migration (deletes all data)
php artisan migrate:fresh

# 2. Run seeder
php artisan db:seed --class=DemoDataSeeder

# 3. Verify
php artisan tinker
User::count()  # Should be 3
Charity::count()  # Should be 1

# 4. Test login
exit
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"donor@example.com","password":"password"}'
```

---

## 📝 WHAT THE SEEDER CREATES

### Users Table:
```
1. System Admin (admin@example.com)
   - Role: admin
   - Status: active
   - Phone: 09171111111

2. Demo Donor (donor@example.com)
   - Role: donor
   - Status: active
   - Phone: 09172222222
   - Address: 123 Donor Street, Manila

3. Charity Admin (charityadmin@example.com)
   - Role: charity_admin
   - Status: active
   - Phone: 09173333333
```

### Charities Table:
```
1. HopeWorks Foundation
   - Owner: Charity Admin (user_id: 3)
   - Status: approved
   - Mission: Transparent community support
   - Address: 456 Charity Ave, Quezon City
   - Region: Metro Manila
   - Category: Community Development
```

### Donation Channels:
```
1. GCash Main
   - Charity: HopeWorks Foundation
   - Type: gcash
   - Number: 09171234567
   - Active: true
```

### Campaigns:
```
1. School Kits 2025
   - Charity: HopeWorks Foundation
   - Target: 250,000
   - Status: published
```

---

## 🚀 RUN THIS COMMAND NOW:

```bash
cd capstone_backend
php artisan db:seed --class=DemoDataSeeder
```

Then test all 3 accounts:
- ✅ admin@example.com
- ✅ donor@example.com
- ✅ charityadmin@example.com

**All should work with password: `password`** 🎉
