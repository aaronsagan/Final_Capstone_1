# Profile & Cover Photo Upload Fix

## Problem Analysis
You're uploading logo and cover photos during charity registration, but they're not showing on the website.

## Investigation Results

### ✅ What's Working
1. **Frontend Upload** - RegisterCharity.tsx correctly sends files:
   - `logo` → stored as `logo_path` in database
   - `cover_image` → stored as `cover_image` in database

2. **Backend Storage** - AuthController.php correctly handles uploads:
   ```php
   // Lines 135-145
   if ($r->hasFile('logo')) {
       $logoPath = $r->file('logo')->store('charity_logos', 'public');
   }
   if ($r->hasFile('cover_image')) {
       $coverPath = $r->file('cover_image')->store('charity_covers', 'public');
   }
   ```

3. **Database Fields** - Charity model has both fields:
   - `logo_path`
   - `cover_image`

4. **Frontend Display** - CharityDashboard shows cover image (line 284-320)

### ❌ Potential Issues

#### Issue 1: Storage Symlink Not Created
Laravel stores files in `storage/app/public/` but serves them from `public/storage/`. You need a symlink.

#### Issue 2: Image Paths May Be Incorrect
The backend saves paths like `charity_logos/abc123.jpg` but frontend expects them at `/storage/charity_logos/abc123.jpg`

#### Issue 3: CORS or File Permissions
Images might be blocked by CORS or file permissions.

## Solution

### Step 1: Create Storage Symlink (CRITICAL)

Run this command in your backend directory:

```powershell
cd capstone_backend
php artisan storage:link
```

This creates a symlink from `public/storage` → `storage/app/public`

**Expected output:**
```
The [public/storage] link has been connected to [storage/app/public].
The links have been created.
```

### Step 2: Verify File Permissions

Make sure the storage directory is writable:

```powershell
# Windows (run as Administrator if needed)
icacls storage /grant Users:F /T
icacls public /grant Users:F /T
```

### Step 3: Check If Images Were Actually Uploaded

1. Navigate to `capstone_backend\storage\app\public\`
2. Check if these folders exist with your images:
   - `charity_logos\`
   - `charity_covers\`

If folders are empty, the upload failed during registration.

### Step 4: Test Image Access

After creating the symlink, test if images are accessible:

1. Find an image path from your database:
   ```sql
   SELECT logo_path, cover_image FROM charities WHERE id = YOUR_CHARITY_ID;
   ```

2. Try accessing it in browser:
   ```
   http://127.0.0.1:8000/storage/charity_logos/FILENAME.jpg
   ```

If you get 404, the symlink isn't working.

### Step 5: Re-register If Needed

If images weren't uploaded during registration:
1. The symlink must be created FIRST
2. Then register a new charity account
3. Upload logo and cover photo
4. They should now appear

## Quick Test

### Test 1: Check Symlink Exists
```powershell
cd capstone_backend\public
dir storage
```

You should see a `storage` directory/symlink.

### Test 2: Check Files Exist
```powershell
cd capstone_backend\storage\app\public
dir charity_logos
dir charity_covers
```

You should see your uploaded images.

### Test 3: Access Via URL
Open browser and go to:
```
http://127.0.0.1:8000/storage/charity_logos/
```

You should see a directory listing or 403 (not 404).

## If Images Still Don't Show

### Option A: Check Database
```sql
SELECT id, name, logo_path, cover_image FROM charities;
```

If `logo_path` and `cover_image` are NULL, the upload failed.

### Option B: Check Browser Console
1. Open DevTools (F12)
2. Go to Network tab
3. Refresh the charity dashboard
4. Look for image requests
5. Check if they're 404 or 403

### Option C: Check Backend Logs
```powershell
cd capstone_backend
Get-Content storage\logs\laravel.log -Tail 50
```

Look for upload errors.

## Expected File Structure

After fix:
```
capstone_backend/
├── public/
│   └── storage/  ← SYMLINK to storage/app/public
├── storage/
│   └── app/
│       └── public/
│           ├── charity_logos/
│           │   └── abc123.jpg
│           └── charity_covers/
│               └── def456.jpg
```

## Frontend Display

The images should appear at:
- **Logo:** Various places (navbar, profile, etc.)
- **Cover Image:** Top of charity dashboard as banner

## Next Steps

1. **Run `php artisan storage:link`** ← DO THIS FIRST
2. Check if existing images appear
3. If not, re-register with new images
4. Verify images show on dashboard

---

**Most Common Issue:** Missing storage symlink. Run the artisan command!
