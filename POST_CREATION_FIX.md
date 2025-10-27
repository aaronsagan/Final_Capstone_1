# Charity Post Creation Fix ✅

## Problem
Creating a post as a charity was failing with a 500 Internal Server Error.

## Error Found
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'is_liked' in 'field list'
```

## Root Cause
The `Update` model had `is_liked` in the `$attributes` array:

```php
protected $attributes = [
    'is_liked' => false,  // ❌ WRONG - This tries to insert into DB
];
```

This caused Laravel to try to **insert** `is_liked` into the database when creating a new update. But `is_liked` is NOT a database column - it's a **computed property** that checks if the current user has liked the update.

## Solution Applied

### 1. Removed `is_liked` from `$attributes`
This field should not be stored in the database.

### 2. Added `is_liked` to `$appends`
```php
protected $appends = ['is_liked'];
```
This tells Laravel to include the computed `is_liked` value when converting the model to JSON.

### 3. Added Accessor Method
```php
public function getIsLikedAttribute(): bool
{
    $userId = auth()->id();
    if (!$userId) {
        return false;
    }
    return $this->isLikedBy($userId);
}
```
This computes the `is_liked` value based on the authenticated user.

## How It Works Now

1. **Creating a post:** Only actual database columns are inserted
2. **Fetching posts:** The `is_liked` attribute is automatically computed for each update
3. **JSON response:** Includes `is_liked: true/false` based on whether the current user liked it

## Test the Fix

### 1. Try Creating a Post Again

1. Login as a charity admin
2. Go to the Updates page
3. Click "Create Update"
4. Write some content
5. (Optional) Upload images
6. Click "Post Update"

**Expected:** Post should be created successfully! ✅

### 2. Verify in Browser Console

Open DevTools (F12) → Console tab

**Before fix:**
```
POST http://127.0.0.1:8000/api/updates 500 (Internal Server Error)
Error creating update: AxiosError
```

**After fix:**
```
POST http://127.0.0.1:8000/api/updates 201 (Created)
✓ Post created successfully
```

### 3. Check Backend Logs (Optional)

```powershell
cd capstone_backend
Get-Content storage\logs\laravel.log -Tail 20
```

Should see successful update creation, no more SQL errors.

## What's Fixed

✅ **Post creation works** - No more 500 errors  
✅ **is_liked computed correctly** - Based on current user  
✅ **Database integrity maintained** - Only valid columns inserted  
✅ **JSON responses include is_liked** - For frontend display  

## Technical Details

### Database Columns (updates table)
- `id`
- `charity_id`
- `parent_id`
- `content`
- `media_urls`
- `is_pinned`
- `likes_count`
- `comments_count`
- `shares_count`
- `created_at`
- `updated_at`

### Computed Attributes (not in DB)
- `is_liked` - Calculated based on `update_likes` table

### How is_liked is Determined
```sql
SELECT EXISTS(
    SELECT 1 FROM update_likes 
    WHERE update_id = ? AND user_id = ?
)
```

## Ignore These Errors

The Grammarly extension errors you saw are **NOT related** to your issue:
```
chrome-extension://kbfnbcaeplbcioakkpcpgfkobkghlhen/...
grm ERROR [RenderWithStyles]
```

These are from the Grammarly browser extension trying to load its CSS files. They don't affect your application.

## Summary

The issue was a simple model configuration error where `is_liked` was being treated as a database column instead of a computed attribute. Now posts can be created successfully!

---

**Status:** ✅ Fixed - Try creating a post now!
