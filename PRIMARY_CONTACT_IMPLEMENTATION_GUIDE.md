# Primary Contact Implementation Guide

## ✅ Implementation Complete

This guide documents the complete implementation of the structured Primary Contact section for the Charity Registration Form.

---

## 📦 Files Created/Modified

### Backend Files

#### 1. **Migration** (NEW)
**File**: `capstone_backend/database/migrations/2025_10_20_000002_update_charities_primary_contact_fields.php`

**What it does**:
- Adds 8 new structured contact fields to `charities` table
- Migrates existing data from old fields to new fields
- Preserves backward compatibility

**Fields Added**:
```php
primary_first_name (string, 50)
primary_middle_initial (string, 1, nullable)
primary_last_name (string, 50)
primary_position (string, 100, nullable)
primary_email (string, 100)
primary_phone (string, 15)
primary_alt_phone (string, 15, nullable)
preferred_contact_method (enum: email, phone, both, nullable)
```

#### 2. **Form Request** (NEW)
**File**: `capstone_backend/app/Http/Requests/CharityRegistrationRequest.php`

**What it does**:
- Centralized validation rules for charity registration
- Custom error messages
- Regex validation for names (letters only)
- Phone validation (09XXXXXXXXX format)
- Email validation

**Key Validations**:
```php
'primary_first_name' => 'required|string|max:50|regex:/^[A-Za-zÑñ\s]+$/'
'primary_middle_initial' => 'nullable|string|max:1|regex:/^[A-Za-zÑñ]$/'
'primary_phone' => 'required|regex:/^(09|\+639)\d{9}$/'
```

#### 3. **Model Update** (MODIFIED)
**File**: `capstone_backend/app/Models/Charity.php`

**Changes**:
- Added new contact fields to `$fillable` array

---

### Frontend Files

#### 4. **Primary Contact Component** (NEW)
**File**: `capstone_frontend/src/components/forms/PrimaryContactFields.tsx`

**Features**:
- ✅ Structured name fields (First, M.I., Last)
- ✅ Position/Role field
- ✅ Email with validation
- ✅ Phone with auto-formatting (11 digits only)
- ✅ Alternate phone (optional)
- ✅ Preferred contact method dropdown
- ✅ Real-time input validation
- ✅ Clean 2-column responsive layout
- ✅ Error messages under each field

**Input Restrictions**:
- Names: Letters and spaces only (including Ñ)
- Middle Initial: Single letter, auto-uppercase
- Phone: Numbers only, max 11 digits
- Email: Standard email validation

#### 5. **Registration Form** (MODIFIED)
**File**: `capstone_frontend/src/pages/auth/RegisterCharity.tsx`

**Changes**:
- ✅ Imported `PrimaryContactFields` component
- ✅ Updated `formData` state with new fields
- ✅ Added comprehensive validation rules
- ✅ Replaced old contact section with new component

---

## 🚀 How to Deploy

### Step 1: Run Migration

```bash
cd capstone_backend
php artisan migrate
```

**Expected Output**:
```
Migrating: 2025_10_20_000002_update_charities_primary_contact_fields
Migrated:  2025_10_20_000002_update_charities_primary_contact_fields (XX.XXms)
```

### Step 2: Verify Database Changes

Open MySQL Workbench and check the `charities` table:

```sql
DESCRIBE charities;
```

**You should see these new columns**:
- `primary_first_name`
- `primary_middle_initial`
- `primary_last_name`
- `primary_position`
- `primary_email`
- `primary_phone`
- `primary_alt_phone`
- `preferred_contact_method`

### Step 3: Test Frontend

1. Navigate to charity registration page
2. Fill in the Primary Contact section
3. Verify validations work:
   - Try entering numbers in name fields → Should be blocked
   - Try entering more than 1 letter in M.I. → Should be blocked
   - Try entering letters in phone → Should be blocked
   - Try entering invalid email → Should show error

---

## 🧪 Testing Checklist

### Frontend Validation Tests

- [ ] **First Name**
  - [ ] Required field shows error when empty
  - [ ] Only accepts letters and spaces
  - [ ] Blocks numbers and special characters
  - [ ] Max 50 characters

- [ ] **Middle Initial**
  - [ ] Optional (no error when empty)
  - [ ] Only accepts single letter
  - [ ] Auto-converts to uppercase
  - [ ] Blocks multiple characters

- [ ] **Last Name**
  - [ ] Required field shows error when empty
  - [ ] Only accepts letters and spaces
  - [ ] Blocks numbers and special characters
  - [ ] Max 50 characters

- [ ] **Position**
  - [ ] Optional field
  - [ ] Accepts any text
  - [ ] Max 100 characters

- [ ] **Email**
  - [ ] Required field shows error when empty
  - [ ] Validates email format
  - [ ] Shows error for invalid emails
  - [ ] Max 100 characters

- [ ] **Phone Number**
  - [ ] Required field shows error when empty
  - [ ] Only accepts numbers
  - [ ] Must start with "09"
  - [ ] Must be exactly 11 digits
  - [ ] Shows format hint below field

- [ ] **Alternate Phone**
  - [ ] Optional field
  - [ ] Same validation as primary phone when filled
  - [ ] Can be left empty

- [ ] **Preferred Contact Method**
  - [ ] Optional dropdown
  - [ ] Shows: Email, Phone, Both options

### Backend Validation Tests

Test with Postman or similar tool:

```json
POST /api/auth/register-charity

{
  "primary_first_name": "Juan",
  "primary_middle_initial": "D",
  "primary_last_name": "Dela Cruz",
  "primary_position": "Executive Director",
  "primary_email": "juan@charity.org",
  "primary_phone": "09171234567",
  "primary_alt_phone": "09281234567",
  "preferred_contact_method": "both",
  ...
}
```

**Test Cases**:
1. ✅ Valid data → Should save successfully
2. ❌ Missing first_name → Should return validation error
3. ❌ Numbers in first_name → Should return validation error
4. ❌ Multiple letters in middle_initial → Should return validation error
5. ❌ Invalid email format → Should return validation error
6. ❌ Phone not starting with 09 → Should return validation error
7. ❌ Phone with less than 11 digits → Should return validation error

---

## 📊 Database Schema

### Before (Old Structure)
```sql
contact_person_name VARCHAR(255)
contact_email VARCHAR(255)
contact_phone VARCHAR(255)
```

### After (New Structure)
```sql
primary_first_name VARCHAR(50)
primary_middle_initial VARCHAR(1) NULLABLE
primary_last_name VARCHAR(50)
primary_position VARCHAR(100) NULLABLE
primary_email VARCHAR(100)
primary_phone VARCHAR(15)
primary_alt_phone VARCHAR(15) NULLABLE
preferred_contact_method ENUM('email','phone','both') NULLABLE
```

---

## 🔄 Data Migration

The migration automatically migrates existing data:

**Example**:
```
Old: contact_person_name = "Juan Dela Cruz"
     contact_email = "juan@charity.org"
     contact_phone = "09171234567"

New: primary_first_name = "Juan"
     primary_last_name = "Dela Cruz"
     primary_email = "juan@charity.org"
     primary_phone = "09171234567"
```

**Note**: Middle initial cannot be extracted from old data, so it will be NULL for migrated records.

---

## 🎨 UI/UX Features

### Layout
```
Primary Contact
┌─────────────────────────────────────────────────────────┐
│ [First Name (5 cols)] [M.I. (2 cols)] [Last Name (5)]  │
│ [Position / Role (full width)]                          │
│ [Email Address (6 cols)] [Phone Number (6 cols)]       │
│ [Alternate Phone (6)] [Preferred Method (6)]           │
└─────────────────────────────────────────────────────────┘
```

### Visual Feedback
- ✅ Red border on invalid fields
- ✅ Error messages below each field
- ✅ Format hints for phone numbers
- ✅ Auto-uppercase for middle initial
- ✅ Character counters (implicit via maxLength)

### Accessibility
- ✅ Proper `<label>` elements
- ✅ Required fields marked with asterisk
- ✅ Error messages linked to inputs
- ✅ Keyboard navigable
- ✅ Clear placeholder text

---

## 🔧 Customization Options

### Add More Contact Methods

Edit the enum in migration:
```php
$table->enum('preferred_contact_method', ['email', 'phone', 'both', 'sms', 'whatsapp'])
```

Update frontend dropdown:
```tsx
<option value="sms">SMS</option>
<option value="whatsapp">WhatsApp</option>
```

### Change Phone Format

For international format (+63):
```php
'primary_phone' => 'required|regex:/^\+639\d{9}$/'
```

### Add Landline Support

Add new field:
```php
$table->string('primary_landline', 20)->nullable();
```

---

## 🐛 Troubleshooting

### Issue: Migration fails with "column already exists"

**Solution**:
```bash
php artisan migrate:rollback --step=1
php artisan migrate
```

### Issue: Frontend validation not working

**Solution**:
1. Check browser console for errors
2. Verify component is imported correctly
3. Clear browser cache (Ctrl+Shift+R)

### Issue: Phone validation too strict

**Solution**: Update regex in both frontend and backend to accept your desired format.

### Issue: Old data not migrated

**Solution**: The migration uses `SUBSTRING_INDEX` which works for simple "First Last" names. For complex names, you may need to manually update:

```sql
UPDATE charities 
SET primary_first_name = 'Corrected First',
    primary_last_name = 'Corrected Last'
WHERE id = X;
```

---

## 📝 Next Steps (Optional Enhancements)

### 1. Add Contact Person Photo
```php
$table->string('primary_contact_photo')->nullable();
```

### 2. Add Multiple Contacts
Create a separate `charity_contacts` table for multiple contact persons.

### 3. Add Contact Verification
Send verification email/SMS to confirm contact details.

### 4. Add Emergency Contact
```php
$table->string('emergency_contact_name')->nullable();
$table->string('emergency_contact_phone')->nullable();
```

---

## ✨ Summary

### What Was Implemented

✅ **Backend**:
- Database migration with 8 new fields
- Automatic data migration from old fields
- Comprehensive validation rules
- Form Request class for clean validation

✅ **Frontend**:
- Reusable `PrimaryContactFields` component
- Real-time input validation
- Clean, responsive 2-column layout
- User-friendly error messages
- Auto-formatting for phone and middle initial

✅ **Features**:
- Structured name collection (First, M.I., Last)
- Position/role tracking
- Dual phone numbers (primary + alternate)
- Preferred contact method selection
- Full validation on both frontend and backend
- Backward compatibility with data migration

---

## 🎉 Done!

Your Charity Registration Form now has a professional, production-ready Primary Contact section with comprehensive validation and clean data structure!

**Test it now**: Navigate to `/register-charity` and fill out the Primary Contact section.
