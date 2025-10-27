# ✅ Admin Login Fix - Invalid Credentials

## 🔍 Problem Diagnosis

You're getting **401 Unauthorized - Invalid Credentials** when trying to login.

**Error:** `POST http://127.0.0.1:8000/api/auth/login 401 (Unauthorized)`

---

## ✅ Solution: Correct Admin Credentials

### **The correct admin credentials are:**

```
Email:    admin@example.com      ← NOT "admin@example" 
Password: password
```

**Important:** You need the full email with **`.com`** at the end!

---

## 🔧 If Login Still Fails, Run Database Seeder

If the account doesn't exist or password is wrong, you need to seed the database:

### **Step 1: Navigate to Backend Directory**
```bash
cd c:\Users\sagan\Capstone\capstone_backend
```

### **Step 2: Run Database Seeder**
```bash
php artisan db:seed --class=UsersSeeder
```

This will create/update these test accounts:

| Role          | Email                 | Password   | Status |
|---------------|----------------------|------------|--------|
| **Admin**     | admin@example.com    | password   | Active |
| **Donor**     | donor@example.com    | password   | Active |
| **Charity**   | charity@example.com  | password   | Active |

---

## 🧪 Test the Login

### **Option 1: Quick Test in Browser**

1. Go to: `http://localhost:5173/auth/login`
2. Enter:
   - **Email:** `admin@example.com`
   - **Password:** `password`
3. Click "Sign in"
4. You should be redirected to `/admin` dashboard

### **Option 2: Test with API Directly**

Open terminal and run:

```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"admin@example.com\",\"password\":\"password\"}"
```

**Expected Response:**
```json
{
  "token": "eyJ0eXAiOiJKV1...",
  "user": {
    "id": 1,
    "name": "System Admin",
    "email": "admin@example.com",
    "role": "admin",
    "status": "active"
  }
}
```

---

## 🔄 If Database is Empty

If your database has no users at all:

### **Full Database Reset & Seed:**

```bash
# Navigate to backend
cd c:\Users\sagan\Capstone\capstone_backend

# Reset database (⚠️ WARNING: This deletes all data!)
php artisan migrate:fresh

# Seed with demo users
php artisan db:seed

# Or specific seeder only
php artisan db:seed --class=UsersSeeder
```

---

## 📋 All Demo Accounts

After running the seeder, you'll have these accounts:

### **1. Admin Account**
```
Email:    admin@example.com
Password: password
Role:     admin
Access:   /admin dashboard
```

### **2. Donor Account**
```
Email:    donor@example.com
Password: password
Role:     donor
Access:   /donor dashboard
```

### **3. Charity Account**
```
Email:    charity@example.com
Password: password
Role:     charity_admin
Access:   /charity dashboard
```

---

## ✅ Summary

### **Your Issue:**
- You tried to login with `admin@example` ❌
- Correct email is `admin@example.com` ✅

### **Quick Fix:**
1. Use **full email:** `admin@example.com`
2. Use **password:** `password`
3. If still fails, run: `php artisan db:seed --class=UsersSeeder`

---

## 🎯 Verification Steps

### ✅ **Step 1: Check Backend is Running**
```bash
# Should show Laravel welcome or API routes
curl http://127.0.0.1:8000
```

### ✅ **Step 2: Check Database Connection**
```bash
php artisan migrate:status
```

### ✅ **Step 3: Verify User Exists**
```bash
php artisan tinker
>>> \App\Models\User::where('email', 'admin@example.com')->first();
# Should return the admin user object
```

### ✅ **Step 4: Test Login API**
```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"admin@example.com\",\"password\":\"password\"}"
```

---

## 🚨 Common Mistakes

| ❌ Wrong                  | ✅ Correct              |
|---------------------------|-------------------------|
| admin@example             | admin@example.com       |
| admin                     | admin@example.com       |
| Password                  | password (lowercase)    |
| PASSWORD                  | password (lowercase)    |

---

**The admin account DOES exist in your seeder! Just use the correct full email address: `admin@example.com` with password `password`**
