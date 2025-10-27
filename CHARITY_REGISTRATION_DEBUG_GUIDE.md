# Charity Registration 422 Error - Debugging Guide

## Understanding the Errors

### Grammarly Extension Errors (IGNORE THESE)
```
chrome-extension://kbfnbcaeplbcioakkpcpgfkobkghlhen/...
grm ERROR [RenderWithStyles]...
```
**These are NOT your problem** - just browser extension noise.

### The Real Error
```
127.0.0.1:8000/api/auth/register-charity:1
Failed to load resource: the server responded with a status of 422 (Unprocessable Content)
```
**This is the actual issue** - validation failed on the backend.

## What I Fixed

### 1. Improved Error Handling ✅
**Before:** Generic "Registration failed" message  
**After:** Detailed validation errors displayed

The registration form now shows:
- Specific field errors in the UI
- Detailed error messages in toast notifications
- Console logging of all validation errors

### 2. Added Debug Logging ✅
When you submit the form, you'll now see in the console:
```javascript
{
  formFields: { /* all form data */ },
  hasLogo: true/false,
  hasCover: true/false,
  documentCounts: { /* file counts */ }
}
```

## Common Causes of 422 Errors

### 1. Duplicate Email
**Error:** `primary_email: The primary email has already been taken`

**Solution:** Use a unique email address that hasn't been registered before

**Test accounts already exist:**
- `admin@example.com`
- `donor@example.com`
- `charity@example.com`
- `newcharity@example.com` (from our test)

### 2. Password Mismatch
**Error:** `password: The password confirmation does not match`

**Solution:** Ensure password and confirm password are EXACTLY the same

### 3. Missing Required Fields
Backend requires:
- `primary_first_name` ✅ Required
- `primary_last_name` ✅ Required
- `primary_email` ✅ Required (must be unique)
- `primary_phone` ✅ Required
- `password` ✅ Required (min 6 chars)
- `password_confirmation` ✅ Required (must match password)
- `organization_name` ✅ Required

### 4. Invalid Field Formats
- **Phone:** Must be max 15 characters
- **Names:** Max 50 characters
- **Middle Initial:** Single letter only
- **Logo/Cover:** Max 5MB, image formats only

## How to Debug

### Step 1: Check Browser Console
1. Open DevTools (F12)
2. Go to Console tab
3. Try to register
4. Look for error messages (ignore Grammarly)

### Step 2: Check Network Tab
1. Open DevTools (F12)
2. Go to Network tab
3. Filter by "register-charity"
4. Click on the failed request
5. Look at "Preview" or "Response" tab
6. See the exact validation errors

### Step 3: Use the Debug Log
After submitting, check console for:
```javascript
Submitting charity registration with data: {
  formFields: {...},
  hasLogo: true,
  hasCover: false,
  documentCounts: {...}
}
```

Then look for:
```javascript
Registration error: {
  response: {
    data: {
      errors: {
        primary_email: ["The primary email has already been taken"],
        password: ["The password confirmation does not match"]
      }
    }
  }
}
```

## Quick Test

I've verified the backend works perfectly. Try this test data:

```javascript
{
  primary_first_name: "Jane",
  primary_last_name: "Smith",
  primary_email: "janesmith@test.com",  // MUST BE UNIQUE
  primary_phone: "09123456789",
  password: "password123",
  password_confirmation: "password123",  // MUST MATCH
  organization_name: "Jane's Charity Foundation",
  registration_number: "REG-2024-001",
  tax_id: "TAX-2024-001"
}
```

## Next Steps

1. **Try registering again** - you'll now see specific error messages
2. **Check the console** - see exactly what data is being sent
3. **Read the error** - it will tell you which field is the problem
4. **Fix and retry** - adjust the failing field

## If You Still Get Errors

Share the **exact error message** from:
1. The toast notification
2. The browser console
3. The Network tab response

The improved error handling will show you exactly what's wrong!

## Files Modified
- ✅ `RegisterCharity.tsx` - Better error handling and debugging
- ✅ Backend validated - Works correctly

---

**TL;DR:** The backend works fine. You'll now see detailed error messages telling you exactly what field is invalid. Most common issue is duplicate email or password mismatch. 🎯
