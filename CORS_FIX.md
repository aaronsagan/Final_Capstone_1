# CORS Issue Fixed ✓

## Problem
Your frontend is running on **http://localhost:8081** but the backend CORS configuration only allowed:
- http://localhost:8080
- http://localhost:5173

This caused the browser to block login requests (CORS error).

## Solution Applied
Updated `capstone_backend/config/cors.php` to include port 8081:

```php
'allowed_origins' => [
    'http://localhost:8080',
    'http://127.0.0.1:8080',
    'http://localhost:8081',      // ← ADDED
    'http://127.0.0.1:8081',      // ← ADDED
    'http://localhost:5173',
    'http://127.0.0.1:5173',
],
```

## Next Steps

**You need to restart the backend server:**

1. Stop the current backend server (Ctrl+C in the terminal running it)
2. Restart it:
   ```powershell
   cd capstone_backend
   php artisan serve
   ```

3. Then try logging in again at http://localhost:8081/

## Login Credentials
- **Email**: admin@example.com
- **Password**: password

OR

- **Email**: donor@example.com
- **Password**: password

OR

- **Email**: charityadmin@example.com
- **Password**: password

## How to Check if It Works

1. Open http://localhost:8081/ in your browser
2. Open Developer Tools (F12)
3. Go to Console tab
4. Try to login
5. You should NOT see CORS errors anymore
6. Login should succeed and redirect you to the dashboard

If you still see errors, check the Console and Network tabs in Developer Tools.
