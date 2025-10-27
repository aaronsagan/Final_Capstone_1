# Donor Profile "Please login first" Error - FIXED

## Issue
When editing the donor profile at `http://localhost:8080/donor/profile`, users were getting a "Please login first" error even though they were already signed in.

## Root Cause
The profile update functions were only checking `localStorage.getItem('token')` for the authentication token, but the auth system stores tokens with the key `'auth_token'` and can store them in either:
- `localStorage` (if "Remember Me" was checked during login)
- `sessionStorage` (if "Remember Me" was NOT checked)

This mismatch caused the token lookup to fail, resulting in the "Please login first" error.

## Files Fixed

### 1. **DonorProfile.tsx**
- Fixed `handleSave()` function (line 35)
- Fixed `handleChangePassword()` function (line 101)
- Changed from: `localStorage.getItem('token')`
- Changed to: `localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token')`

### 2. **OrganizationProfile.tsx** (Charity)
- Fixed `handleSave()` function (line 105)
- Same token retrieval fix applied

### 3. **CharitySettings.tsx**
- Fixed `handleSaveProfile()` function (line 57)
- Removed non-existent `updateUser` call (line 70)
- Same token retrieval fix applied

### 4. **Admin Dashboard.tsx**
- Fixed `fetchDashboardData()` function (line 75)
- Fixed `handleCharityAction()` function (line 105)
- Fixed `handleUserAction()` function (line 126)
- Same token retrieval fix applied to all admin actions

## Solution Pattern
All API calls that require authentication now use:
```typescript
const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
```

This ensures the token is found regardless of:
1. The storage location (localStorage vs sessionStorage)
2. Whether the user checked "Remember Me" during login

## Testing
To verify the fix:
1. Login as a donor (with or without "Remember Me")
2. Navigate to `/donor/profile`
3. Click "Edit Profile"
4. Upload a photo, edit info
5. Click "Save Changes"
6. Should see "Profile updated successfully" instead of "Please login first"

## Status
✅ **FIXED** - All profile edit pages now correctly retrieve the authentication token from both storage locations.
