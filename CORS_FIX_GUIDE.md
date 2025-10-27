# CORS Error Fix Guide

## Problem
```
Access to XMLHttpRequest at 'http://127.0.0.1:8000/locations/regions' from origin 'http://localhost:8080' 
has been blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present on the requested resource.
```

## ✅ Solutions Applied

### 1. Created Frontend Environment File
**File**: `capstone_frontend/.env`
```env
VITE_API_URL=http://localhost:8000/api
```

### 2. Updated Hook to Use Correct API URL
**File**: `src/hooks/usePhilippineLocations.ts`
- Now uses `axios` with proper API URL
- Reads from `VITE_API_URL` environment variable

---

## 🔧 Additional Steps Required

### Step 1: Restart Frontend Development Server

**IMPORTANT**: After creating the `.env` file, you MUST restart your frontend server for the environment variables to load.

```bash
# Stop the current server (Ctrl+C)
# Then restart:
cd capstone_frontend
npm run dev
```

### Step 2: Verify Backend is Running

Make sure your Laravel backend is running:

```bash
cd capstone_backend
php artisan serve
```

Should show:
```
Server running on [http://127.0.0.1:8000]
```

### Step 3: Clear Browser Cache

Sometimes browsers cache CORS errors. Try:
1. Open DevTools (F12)
2. Right-click the refresh button
3. Select "Empty Cache and Hard Reload"

Or use Incognito/Private mode.

---

## 🧪 Test the API Directly

### Test in Browser
Open your browser and navigate to:
```
http://localhost:8000/api/locations/regions
```

You should see JSON response with regions data.

### Test with cURL
```bash
curl http://localhost:8000/api/locations/regions
```

---

## ✅ Verification Checklist

- [ ] `.env` file created in `capstone_frontend/`
- [ ] Frontend server restarted (IMPORTANT!)
- [ ] Backend server is running
- [ ] Can access `http://localhost:8000/api/locations/regions` in browser
- [ ] Browser cache cleared
- [ ] Regions dropdown now shows regions

---

## 🔍 If Still Not Working

### Check 1: Verify Environment Variable is Loaded

Add this temporarily to your component:
```typescript
console.log('API URL:', import.meta.env.VITE_API_URL);
```

Should log: `http://localhost:8000/api`

### Check 2: Check Network Tab

1. Open DevTools → Network tab
2. Reload the page
3. Look for the request to `/locations/regions`
4. Check:
   - Request URL (should be `http://localhost:8000/api/locations/regions`)
   - Status (should be 200)
   - Response (should have JSON data)

### Check 3: Verify CORS Middleware

Ensure Laravel CORS middleware is enabled in `bootstrap/app.php` or `app/Http/Kernel.php`:

```php
// Should have:
\Fruitcake\Cors\HandleCors::class,
```

### Check 4: Check Laravel Logs

```bash
tail -f capstone_backend/storage/logs/laravel.log
```

Look for any errors when the request is made.

---

## 🎯 Common Issues

### Issue: "VITE_API_URL is undefined"
**Solution**: Restart frontend server after creating `.env` file

### Issue: "404 Not Found"
**Solution**: Check that routes are registered in `routes/api.php`

### Issue: "500 Internal Server Error"
**Solution**: Check Laravel logs and ensure `ph_locations.json` exists

### Issue: Still getting CORS error
**Solution**: 
1. Verify `config/cors.php` has `http://localhost:8080` in `allowed_origins`
2. Clear Laravel config cache: `php artisan config:clear`
3. Restart Laravel server

---

## 📝 Quick Fix Commands

```bash
# 1. Create .env file (if not done automatically)
cd capstone_frontend
echo "VITE_API_URL=http://localhost:8000/api" > .env

# 2. Restart frontend (REQUIRED!)
npm run dev

# 3. In another terminal, ensure backend is running
cd capstone_backend
php artisan config:clear
php artisan serve

# 4. Test API
curl http://localhost:8000/api/locations/regions
```

---

## ✨ Expected Result

After following these steps, you should see:
- Regions dropdown populated with Philippine regions
- No CORS errors in console
- Selecting a region loads provinces
- Selecting a province loads cities

---

## 🆘 Still Having Issues?

Check these files are correct:

1. **Frontend Hook**: `src/hooks/usePhilippineLocations.ts`
   - Should import `axios`
   - Should use `import.meta.env.VITE_API_URL`

2. **Backend Routes**: `routes/api.php`
   - Should have location routes registered

3. **Backend Data**: `database/data/ph_locations.json`
   - Should exist and have valid JSON

4. **CORS Config**: `config/cors.php`
   - Should allow `http://localhost:8080`

5. **Frontend .env**: `.env` in frontend root
   - Should have `VITE_API_URL=http://localhost:8000/api`
