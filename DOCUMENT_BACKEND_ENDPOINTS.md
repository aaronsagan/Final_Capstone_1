# Document View & Download Backend Endpoints

## 🎯 Issue Fixed
**Problem:** Frontend was getting 404 errors when trying to view or download documents.
**Solution:** Created backend endpoints for document viewing and downloading.

---

## 📋 Implementation

### 1. DocumentController Created
**File:** `capstone_backend/app/Http/Controllers/DocumentController.php`

**Methods:**
- `view(CharityDocument $document)` - Returns document URL for viewing
- `download(CharityDocument $document)` - Streams file for download
- `getMimeType($extension)` - Helper for proper content types

### 2. Routes Added
**File:** `capstone_backend/routes/api.php`

```php
// Document viewing and downloading (authenticated users)
Route::middleware(['auth:sanctum'])->group(function(){
  Route::get('/documents/{document}/view', [DocumentController::class,'view']);
  Route::get('/documents/{document}/download', [DocumentController::class,'download']);
});
```

**Endpoints:**
- `GET /api/documents/{document}/view` - Get document URL
- `GET /api/documents/{document}/download` - Download document file

---

## 🔧 How It Works

### View Endpoint
**Request:**
```
GET /api/documents/2/view
Authorization: Bearer {token}
```

**Response:**
```json
{
  "fileUrl": "charity_documents/document.pdf",
  "fileName": "Tax Document",
  "fileType": "pdf"
}
```

**Features:**
- ✅ Checks if file exists in storage
- ✅ Returns file path for frontend to construct full URL
- ✅ Returns file metadata (name, type)
- ✅ Returns 404 if file not found

### Download Endpoint
**Request:**
```
GET /api/documents/2/download
Authorization: Bearer {token}
```

**Response:**
- Binary file stream
- Proper Content-Type header
- Content-Disposition: attachment

**Features:**
- ✅ Checks if file exists
- ✅ Streams file directly to browser
- ✅ Sets proper MIME type
- ✅ Forces download with correct filename
- ✅ Returns 404 if file not found

---

## 🔒 Security

### Authentication
- Both endpoints require `auth:sanctum` middleware
- Only authenticated users can view/download documents
- Token must be valid and not expired

### File Access
- Files stored in `storage/app/public/`
- Path validation prevents directory traversal
- Only documents in database can be accessed

### Authorization (Future Enhancement)
Currently any authenticated user can access documents. Consider adding:
```php
// In DocumentController
public function view(CharityDocument $document)
{
    // Check if user has permission to view this charity's documents
    if (!$this->canViewDocument(auth()->user(), $document)) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    // ... rest of code
}

private function canViewDocument($user, $document)
{
    // Donors can view verified charity documents
    // Charity admins can view their own documents
    return $user->role === 'donor' || 
           ($user->role === 'charity_admin' && $user->charity_id === $document->charity_id);
}
```

---

## 📁 File Storage Structure

```
storage/
  app/
    public/
      charity_documents/
        charity_1/
          registration.pdf
          tax_document.pdf
        charity_2/
          audit_report.pdf
```

**Database Record:**
```php
CharityDocument {
  id: 2,
  charity_id: 1,
  doc_type: "Tax Document",
  file_path: "charity_documents/charity_1/tax_document.pdf",
  sha256: "...",
  uploaded_by: 5,
  created_at: "2025-10-13",
  updated_at: "2025-10-13"
}
```

---

## 🧪 Testing

### Test View Endpoint
```bash
curl -X GET http://127.0.0.1:8000/api/documents/2/view \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "fileUrl": "charity_documents/charity_1/tax_document.pdf",
  "fileName": "Tax Document",
  "fileType": "pdf"
}
```

### Test Download Endpoint
```bash
curl -X GET http://127.0.0.1:8000/api/documents/2/download \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --output document.pdf
```

**Expected:** File downloads successfully

---

## 🐛 Troubleshooting

### 404 Error - Document Not Found
**Cause:** Document ID doesn't exist in database
**Fix:** Check document ID in frontend matches database

### 404 Error - File Not Found
**Cause:** File path in database doesn't match actual file location
**Fix:** 
```bash
# Check if file exists
ls storage/app/public/charity_documents/

# Check database record
php artisan tinker
>>> CharityDocument::find(2)->file_path
```

### 403 Error - Unauthorized
**Cause:** Token is invalid or expired
**Fix:** Re-login to get fresh token

### CORS Error
**Cause:** CORS headers not set
**Fix:** Already handled by Laravel CORS middleware

---

## 📊 Supported File Types

**MIME Types Configured:**
- PDF: `application/pdf`
- DOC: `application/msword`
- DOCX: `application/vnd.openxmlformats-officedocument.wordprocessingml.document`
- JPG/JPEG: `image/jpeg`
- PNG: `image/png`
- Other: `application/octet-stream` (generic binary)

---

## 🚀 Next Steps

### 1. Test the Endpoints
- Try viewing a document from frontend
- Try downloading a document
- Check browser console for errors

### 2. Verify File Paths
- Ensure documents exist in storage
- Check database file_path matches actual location

### 3. Add Authorization (Optional)
- Implement permission checks
- Restrict access based on user role

### 4. Add Logging (Optional)
```php
public function view(CharityDocument $document)
{
    Log::info('Document viewed', [
        'document_id' => $document->id,
        'user_id' => auth()->id(),
        'charity_id' => $document->charity_id,
    ]);
    // ... rest of code
}
```

---

## ✅ Files Modified

1. **Created:** `app/Http/Controllers/DocumentController.php`
2. **Modified:** `routes/api.php` (added document routes)

---

## 📝 Frontend Integration

The frontend is already configured to use these endpoints:
- View: `GET /api/documents/${doc.id}/view`
- Download: `GET /api/documents/${doc.id}/download`

No frontend changes needed - it should work now!

---

**Status:** ✅ Backend Endpoints Created
**Testing Required:** Yes - test view and download functionality
**Last Updated:** October 16, 2025
