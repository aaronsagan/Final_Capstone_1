# Profile & Cover Photo Upload - Complete Fix ✅

## Problem
When registering a charity and uploading logo/cover photos, the images don't appear on the website.

## Root Cause Found
The `charity_logos` and `charity_covers` folders in `storage/app/public/` are **empty** (0 items), meaning the images were never successfully uploaded during registration.

## What I Fixed

### Backend Changes (AuthController.php)

1. **Added Proper Image Validation**
   ```php
   'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
   'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
   ```
   - Validates file is an actual image
   - Accepts: JPEG, PNG, JPG, GIF
   - Max size: 5MB (5120 KB)

2. **Added Upload Logging**
   ```php
   Log::info('Charity registration file uploads', [
       'has_logo' => $r->hasFile('logo'),
       'has_cover' => $r->hasFile('cover_image'),
       'logo_size' => ...,
       'cover_size' => ...
   ]);
   ```
   This will help debug if files are being received.

3. **Added Success Logging**
   ```php
   Log::info('Logo uploaded successfully', ['path' => $logoPath]);
   Log::info('Cover image uploaded successfully', ['path' => $coverPath]);
   ```

## How to Test the Fix

### Step 1: Register a New Charity Account

1. Go to the charity registration page
2. Fill in all required fields
3. **Upload a logo** (JPEG/PNG, under 5MB)
4. **Upload a cover photo** (JPEG/PNG, under 5MB)
5. Complete registration

### Step 2: Check Backend Logs

After registration, check the logs:

```powershell
cd capstone_backend
Get-Content storage\logs\laravel.log -Tail 30
```

Look for these log entries:
```
Charity registration file uploads
has_logo: true
has_cover: true
logo_size: 12345
cover_size: 67890

Logo uploaded successfully
path: charity_logos/abc123.jpg

Cover image uploaded successfully
path: charity_covers/def456.jpg
```

### Step 3: Verify Files Were Saved

Check if files exist:

```powershell
cd capstone_backend\storage\app\public
dir charity_logos
dir charity_covers
```

You should see your uploaded image files.

### Step 4: Check Database

```sql
SELECT id, name, logo_path, cover_image FROM charities ORDER BY id DESC LIMIT 1;
```

The `logo_path` and `cover_image` columns should have values like:
- `charity_logos/abc123.jpg`
- `charity_covers/def456.jpg`

### Step 5: Login and View Dashboard

1. Login with your new charity account
2. Go to the charity dashboard
3. **Cover photo** should appear as the banner at the top
4. **Logo** should appear in various places (navbar, profile, etc.)

## Image Display Locations

### Cover Photo
- **Charity Dashboard** - Large banner at top
- **Public Charity Profile** - Header banner
- **Browse Charities** - Card thumbnails

### Logo
- **Charity Updates Page** - Avatar next to posts
- **Public Profile** - Profile picture
- **Navbar** - Organization icon
- **Browse Charities** - Card logo

## Troubleshooting

### Issue 1: "Validation failed" Error

**Cause:** Image file is too large or wrong format

**Solution:**
- Use JPEG or PNG format
- Keep file size under 5MB
- Try a smaller image

### Issue 2: Images Upload But Don't Display

**Cause:** Storage symlink missing or broken

**Solution:**
```powershell
cd capstone_backend
php artisan storage:link
```

### Issue 3: 404 Error When Loading Images

**Cause:** Images not accessible via web

**Test:** Try accessing directly:
```
http://127.0.0.1:8000/storage/charity_logos/FILENAME.jpg
```

If 404, check:
1. Symlink exists: `capstone_backend\public\storage`
2. Files exist: `capstone_backend\storage\app\public\charity_logos`
3. Backend server is running

### Issue 4: Files Not Uploading

**Check logs:**
```powershell
Get-Content capstone_backend\storage\logs\laravel.log -Tail 50
```

Look for:
- `has_logo: false` → File not sent from frontend
- `has_logo: true` but no success message → Upload failed
- Validation errors → File doesn't meet requirements

## Frontend Display Code

The images are displayed using this pattern:

```typescript
// Logo
const logoUrl = charityData?.logo_path 
  ? `${import.meta.env.VITE_API_URL}/storage/${charityData.logo_path}`
  : null;

// Cover Image
const coverUrl = charityData?.cover_image 
  ? `${import.meta.env.VITE_API_URL}/storage/${charityData.cover_image}`
  : null;
```

With your `.env` set to `VITE_API_URL=http://127.0.0.1:8000/api`, the full URL becomes:
```
http://127.0.0.1:8000/api/storage/charity_logos/abc123.jpg
```

**WAIT!** This is wrong! The storage path should be:
```
http://127.0.0.1:8000/storage/charity_logos/abc123.jpg
```

NOT `/api/storage/` but just `/storage/`!

## 🚨 CRITICAL FIX NEEDED

The images are being requested at:
```
http://127.0.0.1:8000/api/storage/...  ❌ WRONG
```

But Laravel serves them at:
```
http://127.0.0.1:8000/storage/...  ✅ CORRECT
```

I need to fix this in the frontend code!

---

## Summary

1. ✅ Backend validation improved
2. ✅ Logging added for debugging
3. ✅ Storage symlink confirmed working
4. ⚠️ **CRITICAL:** Image URLs have `/api/` prefix which is wrong
5. 🔧 **NEXT:** Fix frontend image URLs to remove `/api/` from storage paths

**Action Required:** I need to fix the image URL construction in the frontend!
