# Admin System Guide

## Overview
This document provides a comprehensive guide to the admin system functionality, including setup, features, and testing procedures.

## Admin Credentials

### Test Admin Account
- **Email:** `admin@example.com`
- **Password:** `admin123`
- **Role:** `admin`

### Other Test Accounts
- **Donor Account:**
  - Email: `donor@example.com`
  - Password: `donor123`
  
- **Charity Admin Account:**
  - Email: `charity@example.com`
  - Password: `charity123`

## Setup Instructions

### 1. Backend Setup

#### Generate Application Key
```bash
cd capstone_backend
php artisan key:generate
```

#### Run Database Migrations
```bash
php artisan migrate:fresh
```

#### Seed Test Data
```bash
php artisan db:seed
```

This will create:
- Admin user
- Test donor user
- Test charity admin user
- Sample charities and campaigns (from DemoDataSeeder)

#### Start Backend Server
```bash
php artisan serve
```
The backend will run on `http://127.0.0.1:8000`

### 2. Frontend Setup

#### Install Dependencies
```bash
cd capstone_frontend
npm install
```

#### Configure Environment
Ensure `.env.local` exists with:
```
VITE_API_URL=http://127.0.0.1:8000
```

#### Start Frontend Server
```bash
npm run dev
```
The frontend will run on `http://localhost:8080`

## Admin Features

### 1. Dashboard (`/admin`)
- **Metrics Display:**
  - Total approved charities
  - Active campaigns count
  - Total donations count
  - Total users (if available)
  - Pending verifications count
- **Chart:** Charity registrations trend (mock data)

**API Endpoint:** `GET /api/metrics`

### 2. Charity Management (`/admin/charities`)

#### Features:
- View all registered charities
- Filter by verification status (all/pending/approved/rejected)
- Search by name or email
- View detailed charity information
- Approve pending charities
- Reject charities with reason
- Request additional information

#### API Endpoints:
- `GET /api/admin/charities` - Get all charities (paginated)
- `GET /api/admin/verifications` - Get pending charities only
- `GET /api/charities/{id}` - Get charity details
- `PATCH /api/admin/charities/{id}/approve` - Approve charity
- `PATCH /api/admin/charities/{id}/reject` - Reject charity

#### Workflow:
1. Navigate to Charities page
2. Click eye icon to view charity details
3. Review charity information and documents
4. Choose action:
   - **Approve:** Marks charity as approved, sets verified_at timestamp
   - **Reject:** Marks as rejected, requires rejection reason
   - **Request Info:** Sends notification to charity (placeholder)

### 3. User Management (`/admin/users`)

#### Features:
- View all platform users
- Filter by role (admin/donor/charity_admin)
- Search by name or email
- Suspend active users
- Activate suspended users
- Edit user information (UI ready, backend needs implementation)

#### API Endpoints:
- `GET /api/admin/users` - Get all users (paginated)
- `PATCH /api/admin/users/{id}/suspend` - Suspend user
- `PATCH /api/admin/users/{id}/activate` - Activate user

#### User Status:
- **active:** User can log in and use the platform
- **suspended:** User cannot log in (blocked by auth check)

### 4. Audit Logs (`/admin/logs`)
- **Status:** UI implemented, backend integration pending
- **Purpose:** Track admin actions and system events

### 5. Settings (`/admin/settings`)
- **Status:** UI implemented with mock data
- **Features:** System configuration, email settings, security settings

## Backend Implementation

### Controllers

#### VerificationController
**Location:** `app/Http/Controllers/Admin/VerificationController.php`

**Methods:**
- `index()` - Get pending charities with owner relationship
- `getAllCharities()` - Get all charities with owner relationship
- `getUsers()` - Get all users
- `approve($charity)` - Approve charity verification
- `reject($charity)` - Reject charity verification
- `suspendUser($user)` - Suspend user account
- `activateUser($user)` - Activate user account

### Middleware

#### EnsureRole
**Location:** `app/Http/Middleware/EnsureRole.php`

Protects routes by checking user role:
```php
Route::middleware(['auth:sanctum', 'role:admin'])->group(function() {
    // Admin routes
});
```

### Routes
**Location:** `routes/api.php`

All admin routes are protected by `auth:sanctum` and `role:admin` middleware:
```php
Route::middleware(['auth:sanctum','role:admin'])->group(function(){
  Route::get('/admin/verifications', [VerificationController::class,'index']);
  Route::get('/admin/charities', [VerificationController::class,'getAllCharities']);
  Route::get('/admin/users', [VerificationController::class,'getUsers']);
  Route::patch('/admin/charities/{charity}/approve', [VerificationController::class,'approve']);
  Route::patch('/admin/charities/{charity}/reject', [VerificationController::class,'reject']);
  Route::patch('/admin/users/{user}/suspend', [VerificationController::class,'suspendUser']);
  Route::patch('/admin/users/{user}/activate', [VerificationController::class,'activateUser']);
});
```

## Frontend Implementation

### Services

#### AdminService
**Location:** `src/services/admin.ts`

Handles all admin API calls with automatic token injection:
- `getDashboardMetrics()`
- `getPendingCharities(page)`
- `getAllCharities(page, filters)`
- `getCharityDetails(id)`
- `approveCharity(id, notes)`
- `rejectCharity(id, notes)`
- `getUsers(page, filters)`
- `suspendUser(id)`
- `activateUser(id)`

#### AuthService
**Location:** `src/services/auth.ts`

Enhanced with:
- `getCurrentUser()` - Fetches authenticated user from `/api/me`
- Automatic token management (localStorage/sessionStorage)

### Context

#### AuthContext
**Location:** `src/context/AuthContext.tsx`

**Features:**
- Checks for existing token on mount
- Fetches current user if token exists
- Role-based redirection after login:
  - `admin` → `/admin`
  - `charity_admin` → `/charity`
  - `donor` → `/donor`

### Components

#### AdminLayout
**Location:** `src/components/admin/AdminLayout.tsx`

Provides consistent layout with:
- Sidebar navigation
- Header with user info
- Outlet for nested routes

#### Protected Routes
All admin routes are wrapped with:
```tsx
<ProtectedRoute>
  <RoleGate allow={['admin']}>
    <AdminLayout />
  </RoleGate>
</ProtectedRoute>
```

## Testing Procedures

### 1. Admin Login Test
1. Navigate to `http://localhost:8080/auth/login`
2. Enter admin credentials:
   - Email: `admin@example.com`
   - Password: `admin123`
3. Click "Sign in"
4. Should redirect to `/admin` dashboard
5. Verify metrics are displayed

### 2. Charity Verification Test
1. Login as admin
2. Navigate to "Charities" from sidebar
3. Verify charities list loads
4. Click eye icon on a pending charity
5. Review charity details
6. Test approval:
   - Click "Approve" button
   - Verify success toast
   - Verify charity status updates to "Approved"
7. Test rejection:
   - Click "Reject" button
   - Enter rejection reason
   - Click "Confirm Rejection"
   - Verify success toast

### 3. User Management Test
1. Login as admin
2. Navigate to "Users" from sidebar
3. Verify users list loads
4. Test filtering by role
5. Test search functionality
6. Test suspend user:
   - Click trash icon on active user
   - Verify success toast
   - Verify status changes to "suspended"
7. Test activate user:
   - Click checkmark icon on suspended user
   - Verify success toast
   - Verify status changes to "active"

### 4. Authentication Persistence Test
1. Login as admin
2. Refresh the page
3. Verify you remain logged in
4. Verify user state is maintained
5. Navigate between admin pages
6. Verify no re-authentication required

### 5. Authorization Test
1. Logout from admin account
2. Login as donor (`donor@example.com` / `donor123`)
3. Try to access `/admin` directly
4. Should be redirected or see "Forbidden" message
5. Verify donor can only access `/donor` routes

## Known Issues & Limitations

### Current Limitations:
1. **Audit Logs:** UI ready, backend not implemented
2. **Settings:** Mock data, no persistence
3. **User Edit:** UI ready, backend update endpoint not implemented
4. **Document Viewing:** Placeholder, file serving not implemented
5. **Pagination:** UI shows all results, pagination controls not implemented
6. **Chart Data:** Uses mock data, needs real analytics

### Future Enhancements:
1. Real-time notifications for admin actions
2. Advanced filtering and sorting
3. Bulk operations (approve/reject multiple charities)
4. Export functionality (CSV/PDF reports)
5. Activity timeline for charities and users
6. Email notifications for approvals/rejections
7. Dashboard analytics with real-time data
8. File preview for charity documents

## Troubleshooting

### Issue: 404 Error on Login
**Solution:** Ensure `.env.local` exists in frontend with correct `VITE_API_URL`

### Issue: "No application encryption key"
**Solution:** Run `php artisan key:generate` in backend

### Issue: User not persisting after refresh
**Solution:** Verify `getCurrentUser()` API call succeeds and token is valid

### Issue: "Forbidden" error on admin routes
**Solution:** 
1. Verify user role is 'admin'
2. Check token is being sent in Authorization header
3. Verify middleware is properly configured

### Issue: Empty data on admin pages
**Solution:** Run `php artisan db:seed` to populate test data

## API Response Examples

### Get Dashboard Metrics
```json
{
  "charities": 5,
  "campaigns": 12,
  "donations": 45
}
```

### Get Charities (Paginated)
```json
{
  "data": [
    {
      "id": 1,
      "name": "Hope Foundation",
      "contact_email": "info@hope.org",
      "verification_status": "pending",
      "created_at": "2024-03-01T10:00:00.000000Z",
      "owner": {
        "id": 2,
        "name": "John Doe",
        "email": "john@example.com"
      }
    }
  ],
  "current_page": 1,
  "last_page": 3,
  "per_page": 20,
  "total": 45
}
```

### Approve Charity
```json
{
  "message": "Approved"
}
```

## Security Considerations

1. **Authentication:** All admin routes require valid Sanctum token
2. **Authorization:** Role-based access control via middleware
3. **Token Storage:** Tokens stored in localStorage (remember me) or sessionStorage
4. **CORS:** Ensure backend CORS is configured for frontend origin
5. **Password Hashing:** All passwords hashed using bcrypt
6. **SQL Injection:** Protected by Laravel's query builder
7. **XSS Protection:** React automatically escapes output

## Maintenance

### Adding New Admin Features:
1. Create backend endpoint in `VerificationController` or new controller
2. Add route in `routes/api.php` with proper middleware
3. Add method to `adminService.ts`
4. Create/update frontend page component
5. Add navigation item to `AdminSidebar.tsx`
6. Update this documentation

### Database Maintenance:
```bash
# Reset database and reseed
php artisan migrate:fresh --seed

# Seed only
php artisan db:seed

# Seed specific seeder
php artisan db:seed --class=UsersSeeder
```

## Support

For issues or questions:
1. Check this documentation
2. Review error logs in browser console
3. Check Laravel logs: `storage/logs/laravel.log`
4. Verify API responses using browser DevTools Network tab
