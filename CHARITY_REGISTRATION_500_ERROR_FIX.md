# Charity Registration 500 Error Fixed ✓

## Problem

When submitting the charity registration form, it failed with:
```
POST http://127.0.0.1:8000/api/auth/register-charity 500 (Internal Server Error)
```

## Root Cause

**Database Constraint Violation:**
```
SQLSTATE[23000]: Integrity constraint violation: 19 CHECK constraint failed: doc_type
```

The database `charity_documents` table has an ENUM constraint that only accepts these values:
- `registration`
- `tax`
- `bylaws`
- `audit`
- `other`

But the frontend was sending these values:
- `registration_cert`
- `tax_registration`
- `financial_statement`
- `representative_id`
- `additional_docs`

**Result**: Database rejected the insert because `registration_cert` is not a valid enum value.

## Solution

Added a mapping function in the backend to convert frontend document types to database enum values.

### Code Changes

**File**: `capstone_backend/app/Http/Controllers/AuthController.php`

**Before:**
```php
if ($r->hasFile('documents')) {
    foreach ($r->file('documents') as $index => $file) {
        $docType = $r->input("doc_types.{$index}", 'other');
        $path = $file->store('charity_docs', 'public');
        $hash = hash_file('sha256', $file->getRealPath());
        
        $charity->documents()->create([
            'doc_type'=>$docType,  // ❌ Using frontend value directly
            'file_path'=>$path,
            'sha256'=>$hash,
            'uploaded_by'=>$user->id
        ]);
    }
}
```

**After:**
```php
if ($r->hasFile('documents')) {
    // Map frontend doc types to database enum values
    $docTypeMap = [
        'registration_cert' => 'registration',
        'tax_registration' => 'tax',
        'financial_statement' => 'audit',
        'representative_id' => 'other',
        'additional_docs' => 'other'
    ];
    
    foreach ($r->file('documents') as $index => $file) {
        $frontendDocType = $r->input("doc_types.{$index}", 'other');
        $docType = $docTypeMap[$frontendDocType] ?? 'other';  // ✅ Map to valid enum
        $path = $file->store('charity_docs', 'public');
        $hash = hash_file('sha256', $file->getRealPath());
        
        $charity->documents()->create([
            'doc_type'=>$docType,
            'file_path'=>$path,
            'sha256'=>$hash,
            'uploaded_by'=>$user->id
        ]);
    }
}
```

## Document Type Mapping

| Frontend Value | Database Value | Description |
|----------------|----------------|-------------|
| `registration_cert` | `registration` | Registration certificate |
| `tax_registration` | `tax` | Tax registration document |
| `financial_statement` | `audit` | Financial statement/audit |
| `representative_id` | `other` | Representative ID |
| `additional_docs` | `other` | Additional documents |

## About the Grammarly Errors

The errors you saw like:
```
chrome-extension://kbfnbcaeplbcioakkpcpgfkobkghlhen/src/css/...
grm ERROR [RenderWithStyles]
```

These are **NOT related to the registration failure**. They're from the Grammarly browser extension trying to load its styles. You can safely ignore them or disable Grammarly for localhost.

## Testing

1. **Clear any previous failed attempts** (optional)
2. **Fill out the charity registration form**
3. **Upload all required documents**:
   - Registration certificate
   - Tax registration
   - Financial statement (optional)
   - Representative ID
   - Additional docs (optional)
4. **Submit the form**
5. **Expected result**: 
   - ✅ Success message: "Registration successful. Your charity is pending verification."
   - ✅ Redirect to login page
   - ✅ Documents saved in database with correct types

## Verification

After successful registration, you can verify in the database:

```sql
SELECT id, charity_id, doc_type, file_path 
FROM charity_documents 
ORDER BY id DESC 
LIMIT 5;
```

You should see:
- `doc_type` values as: `registration`, `tax`, `audit`, `other`
- NOT: `registration_cert`, `tax_registration`, etc.

## Why This Happened

1. **Frontend** uses descriptive names for better UX
2. **Database** uses shorter enum values for efficiency
3. **Missing mapping** between the two caused the mismatch
4. **Now fixed** with proper mapping in the controller

The registration should now work perfectly! 🎉
