# Follow/Unfollow Bug Fix

## Issue
When clicking "Follow" or "Unfollow" on charity cards and profile pages, the application showed errors:
```
Failed to load resource: the server responded with a status of 500 (Internal Server Error)
Error toggling follow: SyntaxError: Unexpected token '<', "<!DOCTYPE "... is not valid JSON
```

## Root Causes

### 1. Backend Logic Error
**File:** `capstone_backend/app/Http/Controllers/CharityFollowController.php`

The `toggleFollow` method had inverted logic:
- When toggling, it was setting `followed_at` to `null` when the user WAS following
- The action message was also inverted

**Fixed:**
```php
// Before (incorrect)
$existingFollow->update([
    'is_following' => !$existingFollow->is_following,
    'followed_at' => $existingFollow->is_following ? null : now()
]);
$action = $existingFollow->is_following ? 'followed' : 'unfollowed';

// After (correct)
$newStatus = !$existingFollow->is_following;
$existingFollow->update([
    'is_following' => $newStatus,
    'followed_at' => $newStatus ? now() : null
]);
$action = $newStatus ? 'followed' : 'unfollowed';
```

### 2. Database Schema Issue (CRITICAL)
**Files:** 
- `capstone_backend/database/migrations/2025_10_03_190944_create_charity_follows_table.php`
- Migration: `2025_10_15_211824_make_followed_at_nullable_in_charity_follows_table.php`

The `followed_at` column was defined as `NOT NULL` with `useCurrent()`, but the controller was trying to set it to `null` when unfollowing. This caused a database constraint violation (500 error).

**Fixed:**
- Changed the column to be `nullable()`
- Created and ran a migration to alter the existing table
- Now `followed_at` can be `null` when `is_following` is `false`

### 3. Frontend Error Handling
**Files:** 
- `capstone_frontend/src/pages/donor/CharityProfile.tsx`
- `capstone_frontend/src/components/donor/CharityCard.tsx`

The frontend was not checking if the response was successful before trying to parse JSON. When the backend returned an error (HTML page), it tried to parse HTML as JSON.

**Fixed:**
- Added `response.ok` check before parsing JSON
- Added proper headers including `Accept: application/json`
- Added better error logging to help debug issues
- Improved error messages

```typescript
// Added proper error handling
if (!response.ok) {
    const errorText = await response.text();
    console.error('Follow toggle error:', response.status, errorText);
    throw new Error(`Failed to update follow status (${response.status})`);
}
```

## Changes Made

### Backend
- ✅ Fixed toggle logic in `CharityFollowController.php`
- ✅ Corrected `followed_at` timestamp assignment
- ✅ Fixed action message to reflect actual status
- ✅ **Made `followed_at` column nullable in database** (CRITICAL FIX)
- ✅ Created and ran migration to alter existing table

### Frontend
- ✅ Added response status check before JSON parsing in `CharityProfile.tsx`
- ✅ Added response status check before JSON parsing in `CharityCard.tsx`
- ✅ Added proper request headers (`Accept: application/json`)
- ✅ Improved error handling and logging
- ✅ Better error messages for users

## Testing
To test the fix:
1. **On Charity Cards (Browse Charities page):**
   - Click the follow button (UserPlus icon) - should show success message
   - Click again to unfollow (UserMinus icon) - should show success message
   - Verify the icon toggles and follower count updates

2. **On Charity Profile page:**
   - Navigate to any charity profile page as a donor
   - Click "Follow" - should show success message
   - Click "Following" (to unfollow) - should show success message
   - Verify the button text toggles between "Follow" and "Following"
   - Refresh the page - the follow status should persist

3. **Check browser console:**
   - No more 500 errors
   - No more JSON parsing errors
   - Should see proper success messages

## Additional Notes
- The follow endpoint requires authentication and donor role
- The backend correctly toggles the `is_following` status
- The frontend now properly handles both success and error responses
- The database migration has been applied to make `followed_at` nullable
- **IMPORTANT:** The migration must be run on production/other environments
