# 🔧 Charity Post Creation Troubleshooting Guide

## Issue: "Failed to create post"

### 🚀 Quick Fix Steps

1. **Run the fix script:**
   ```powershell
   .\fix-posts.ps1
   ```

2. **Manual steps if script fails:**
   ```bash
   cd capstone_backend
   php artisan migrate
   php artisan storage:link
   php artisan cache:clear
   ```

### 🔍 Common Causes & Solutions

#### **1. Database Table Missing**
**Problem:** `charity_posts` table doesn't exist
**Solution:**
```bash
php artisan migrate
```

#### **2. No Charity Record for User**
**Problem:** User is charity_admin but no charity record exists
**Solution:**
- Check if user completed charity registration
- Verify charity record exists in database
- Ensure `owner_id` matches the logged-in user

#### **3. Authentication Issues**
**Problem:** User not properly authenticated
**Solution:**
- Ensure user is logged in
- Check token is valid
- Verify user role is `charity_admin`

#### **4. Storage Link Missing**
**Problem:** Image uploads fail
**Solution:**
```bash
php artisan storage:link
```

#### **5. Validation Errors**
**Problem:** Required fields missing
**Solution:**
- Ensure both `title` and `content` are filled
- Check `status` is either 'draft' or 'published'

### 🧪 Debug Steps

#### **Step 1: Check User & Charity**
```sql
-- Check if user exists and has correct role
SELECT id, name, email, role FROM users WHERE email = 'your-charity-email@example.com';

-- Check if charity exists for user
SELECT id, name, owner_id, verification_status FROM charities WHERE owner_id = [USER_ID];
```

#### **Step 2: Test API Endpoint**
```bash
# Test with curl (replace with actual values)
curl -X POST http://localhost:8000/api/posts \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test Post",
    "content": "This is a test post content",
    "status": "published"
  }'
```

#### **Step 3: Check Laravel Logs**
```bash
tail -f storage/logs/laravel.log
```

### 🎯 Expected API Response

#### **Success (201):**
```json
{
  "id": 1,
  "title": "Test Post",
  "content": "This is a test post content",
  "image_path": null,
  "status": "published",
  "published_at": "2024-01-01T12:00:00.000000Z",
  "created_at": "2024-01-01T12:00:00.000000Z",
  "charity": {
    "id": 1,
    "name": "Your Charity Name",
    "logo_path": null
  }
}
```

#### **Error Responses:**
- **401:** `{"error": "User not authenticated"}`
- **404:** `{"error": "No charity found for this user"}`
- **422:** Validation errors
- **500:** Server error with details

### 🔧 Frontend Debugging

#### **Check Network Tab:**
1. Open browser DevTools → Network tab
2. Try creating a post
3. Look for the POST request to `/api/posts`
4. Check request payload and response

#### **Check Console Errors:**
1. Open browser DevTools → Console tab
2. Look for JavaScript errors
3. Check if API calls are being made

### 🎯 Complete Test Flow

1. **Login as charity admin**
2. **Go to Posts & Updates section**
3. **Fill in:**
   - Title: "Test Post"
   - Content: "This is a test post"
   - Status: Published
4. **Click "Publish Post"**
5. **Expected:** Success toast + post appears in list

### 🚨 If Still Failing

#### **Check These Files:**
- `app/Http/Controllers/CharityPostController.php` - Controller logic
- `app/Models/CharityPost.php` - Model definition
- `routes/api.php` - Route registration
- `database/migrations/*_create_charity_posts_table.php` - Table structure

#### **Verify Database:**
```sql
-- Check table exists
SHOW TABLES LIKE 'charity_posts';

-- Check table structure
DESCRIBE charity_posts;

-- Check charity exists
SELECT * FROM charities WHERE owner_id = [USER_ID];
```

#### **Check Permissions:**
- Storage directory writable
- Database connection working
- User has charity_admin role
- Charity record exists and is linked to user

### 📞 Support Information

If the issue persists:
1. Check Laravel logs for detailed error messages
2. Verify all migrations have run
3. Ensure storage link exists
4. Confirm user has associated charity record
5. Test API endpoint directly with curl/Postman

The enhanced error handling in the controller will now provide specific error messages to help identify the exact issue.
