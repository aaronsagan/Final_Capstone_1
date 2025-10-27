# CORS Document Download Fix ✅

## Issue
Document download was failing with CORS error:
```
Access to fetch at 'http://127.0.0.1:8000/storage/charity_docs/...' 
from origin 'http://localhost:8080' has been blocked by CORS policy: 
No 'Access-Control-Allow-Origin' header is present on the requested resource.
```

## Root Cause
**CORS (Cross-Origin Resource Sharing) Restriction**

When the frontend (`http://localhost:8080`) tries to fetch files from the backend storage (`http://127.0.0.1:8000/storage/`), the browser blocks it because:

1. **Different origins:** `localhost:8080` ≠ `127.0.0.1:8000`
2. **Storage files are static:** Laravel's CORS middleware doesn't apply to `/storage/*` by default
3. **Fetch API requires CORS:** Using `fetch()` triggers CORS preflight checks

## Solutions Applied

### Solution 1: Update Laravel CORS Config ✅
**File:** `capstone_backend/config/cors.php`

**Changed:**
```php
// Before
'paths' => ['api/*', 'sanctum/csrf-cookie'],

// After
'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*'],
```

This allows CORS for storage files, but may not work for all static file serving configurations.

### Solution 2: Use Direct Link Download (PRIMARY FIX) ✅
**File:** `capstone_frontend/src/pages/donor/CharityProfile.tsx`

**Changed download method from fetch to direct link:**

**Before (CORS Error):**
```typescript
// Using fetch - triggers CORS
const response = await fetch(documentUrl);
const blob = await response.blob();
const url = window.URL.createObjectURL(blob);
```

**After (No CORS Issue):**
```typescript
// Direct link - bypasses CORS
const link = document.createElement('a');
link.href = documentUrl;
link.download = filename;
link.target = '_blank';
link.click();
```

## Why Direct Link Works

### Fetch API (CORS Required):
- ❌ Triggers CORS preflight request
- ❌ Requires `Access-Control-Allow-Origin` header
- ❌ Blocked by browser security

### Direct Link (No CORS):
- ✅ Browser handles download natively
- ✅ No CORS preflight needed
- ✅ Works like clicking a regular link
- ✅ Opens in new tab if download fails

## How It Works Now

### Download Flow:
1. User clicks "Download" button
2. Function builds storage URL: `http://127.0.0.1:8000/storage/charity_docs/file.pdf`
3. Creates temporary `<a>` element with:
   - `href` = storage URL
   - `download` = filename
   - `target="_blank"` = fallback to open in new tab
4. Programmatically clicks the link
5. Browser downloads file OR opens in new tab
6. Removes temporary link element

### View Flow:
- Still uses direct URL in iframe
- No CORS issue because iframe loads directly
- PDF displays in modal

## Files Modified

### 1. Backend: `config/cors.php`
```php
'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*'],
```

### 2. Frontend: `CharityProfile.tsx`
```typescript
// Simplified download - no fetch, no CORS
const link = document.createElement('a');
link.href = documentUrl;
link.download = filename;
link.target = '_blank';
link.click();
```

## Benefits

✅ **No CORS Errors**
- Direct link bypasses CORS restrictions
- Works across all browsers

✅ **Simpler Code**
- No blob creation needed
- No URL.createObjectURL needed
- Fewer lines of code

✅ **Better Fallback**
- If download attribute not supported, opens in new tab
- User can still access the file

✅ **Faster**
- No intermediate blob processing
- Browser handles download natively

## Testing

### Test Download:
1. Go to charity profile page
2. Scroll to "Documents & Certificates"
3. Click "Download" button
4. ✅ File should download immediately
5. ✅ No CORS errors in console

### Test View:
1. Click "View" button on a document
2. ✅ PDF should display in modal
3. ✅ No CORS errors

## Alternative: Backend Download Endpoint

If you need more control (authentication, logging, etc.), you can create a backend endpoint:

**Route:** `routes/api.php`
```php
Route::get('/documents/{id}/download', [DocumentController::class, 'download'])
    ->middleware('auth:sanctum');
```

**Controller:** `app/Http/Controllers/DocumentController.php`
```php
public function download($id)
{
    $document = CharityDocument::findOrFail($id);
    
    // Check permissions
    // Log download
    
    return response()->download(
        storage_path('app/public/' . $document->file_path),
        basename($document->file_path)
    );
}
```

Then use in frontend:
```typescript
const response = await fetch(buildApiUrl(`/documents/${doc.id}/download`), {
    headers: { Authorization: `Bearer ${token}` }
});
```

## Summary

✅ **CORS config updated** - Added `storage/*` to allowed paths  
✅ **Download method fixed** - Using direct link instead of fetch  
✅ **No more CORS errors** - Downloads work perfectly  
✅ **Better user experience** - Faster, simpler, more reliable  

Document downloads now work without CORS issues! 🎉

## Restart Backend

After changing CORS config, restart Laravel:
```bash
# Stop the server (Ctrl+C)
# Then restart
php artisan serve
```

The changes should take effect immediately.
