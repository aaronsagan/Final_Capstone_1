# How to Clear Browser Cache

## The Issue
Your browser is still using the old cached JavaScript files with the wrong API URLs (`/api/api/`).

## Quick Fix - Hard Refresh

### Windows/Linux:
```
Ctrl + Shift + R
```
or
```
Ctrl + F5
```

### Mac:
```
Cmd + Shift + R
```

## If Hard Refresh Doesn't Work

### Option 1: Clear Cache in DevTools
1. Open DevTools (F12)
2. Right-click the refresh button
3. Select "Empty Cache and Hard Reload"

### Option 2: Clear All Cache
1. Press `Ctrl + Shift + Delete`
2. Select "Cached images and files"
3. Click "Clear data"
4. Refresh the page

### Option 3: Incognito/Private Mode
1. Open an incognito/private window
2. Navigate to your app
3. This bypasses all cache

### Option 4: Restart Dev Server
```powershell
# Stop the frontend server (Ctrl + C)
# Then restart:
cd capstone_frontend
npm run dev
```

## What Was Fixed

### PostCard Component
✅ Fixed charity logo display
- Now uses: `getCharityLogoUrl(logo_path)`
- Before: `${API_URL}/storage/${logo_path}` (wrong)

✅ Fixed post media images
- Now uses: `getStorageUrl(url)`
- Before: `${API_URL}/storage/${url}` (wrong)

### API URLs Fixed
✅ All duplicate `/api/api/` removed
- `/charities/{id}/updates`
- `/charities/{id}/campaigns`
- `/me/donations`
- All other endpoints

## Verify It's Working

After clearing cache, check:
1. ✅ Charity logos appear on posts
2. ✅ Post images/media display correctly
3. ✅ No 404 errors in console
4. ✅ Posts load from all charities

## Still Having Issues?

If you still see `/api/api/` errors:
1. Make sure you cleared cache completely
2. Try incognito mode
3. Check the Network tab in DevTools
4. Look at the actual request URLs being made
