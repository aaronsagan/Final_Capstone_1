# Fix Comment 500 Error

## Problem
Comments are showing a 500 Internal Server Error because the database doesn't have the new `likes_count` column yet.

## Solution

### Option 1: Run Migrations (RECOMMENDED)
This will add the missing database columns and tables.

**PowerShell (Windows)**:
```powershell
cd capstone_backend
php artisan migrate
```

**Or use the script**:
```powershell
.\RUN_MIGRATIONS.ps1
```

This will create:
- `comment_likes` table
- `likes_count` column in `update_comments` table

### Option 2: Temporary Fix (Already Applied)
I've updated the backend code to handle missing `likes_count` gracefully. The comments should now load even without the migration, but the like feature won't work until you run the migrations.

## After Running Migrations

1. **Refresh your browser** - Comments should now load properly
2. **Test the like feature**:
   - Click the heart icon under any comment
   - Heart should fill with red color
   - Like count should appear

## Verify It's Working

### Check Database
```sql
-- Check if likes_count column exists
DESCRIBE update_comments;

-- Check if comment_likes table exists
SHOW TABLES LIKE 'comment_likes';
```

### Check Backend Logs
If still having issues, check Laravel logs:
```
capstone_backend/storage/logs/laravel.log
```

## Common Issues

### Issue: "Column not found: likes_count"
**Solution**: Run the migrations
```bash
cd capstone_backend
php artisan migrate
```

### Issue: "Table 'comment_likes' doesn't exist"
**Solution**: Run the migrations (same as above)

### Issue: Migration fails
**Solution**: Check if you're in the right directory and database is running
```bash
# Check database connection
php artisan tinker
DB::connection()->getPdo();
```

## What the Migrations Do

### Migration 1: Create comment_likes table
```sql
CREATE TABLE comment_likes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comment_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY (comment_id, user_id),
    FOREIGN KEY (comment_id) REFERENCES update_comments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Migration 2: Add likes_count to update_comments
```sql
ALTER TABLE update_comments 
ADD COLUMN likes_count INT UNSIGNED DEFAULT 0 AFTER content;
```

## Testing After Fix

1. ✅ Comments load without errors
2. ✅ Can view existing comments
3. ✅ Can add new comments
4. ✅ Can like/unlike comments
5. ✅ Like count displays correctly
6. ✅ Heart icon fills when liked
