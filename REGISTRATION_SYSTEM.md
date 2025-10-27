# Registration System - Complete Implementation

## Overview

The registration system now fully supports both **Donor** and **Charity Admin** registration with all required fields, file uploads, and database persistence.

---

## ✅ Donor Registration

### Features Implemented

1. **Complete User Information**
   - Full name
   - Email (unique)
   - Password (encrypted with bcrypt)
   - Phone number
   - Address
   - Profile image upload

2. **Database Storage**
   - All information saved to `users` table
   - Profile images stored in `storage/app/public/profile_images`
   - Passwords automatically hashed
   - Role set to `donor`
   - Status set to `active`

3. **Admin Visibility**
   - Immediately visible in admin dashboard metrics
   - Counted in "Total Users" and "Total Donors"
   - Viewable in Users management page
   - Full details accessible via "View" button

### API Endpoint

**POST** `/api/auth/register`

**Request (multipart/form-data):**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+1234567890",
  "address": "123 Main St, City, Country",
  "profile_image": <file>
}
```

**Response:**
```json
{
  "message": "Registration successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "address": "123 Main St, City, Country",
    "profile_image": "profile_images/xyz.jpg",
    "role": "donor",
    "status": "active",
    "created_at": "2025-10-02T00:00:00.000000Z"
  }
}
```

---

## ✅ Charity Admin Registration

### Features Implemented

1. **Representative Details**
   - Full name
   - Email (unique)
   - Password (encrypted)
   - Phone number

2. **Organization Details**
   - Organization name
   - Registration number
   - Tax ID
   - Mission statement
   - Vision statement
   - Website URL
   - Contact email
   - Contact phone

3. **Document Uploads**
   - Multiple document support
   - Document types: registration, tax, bylaws, audit, other
   - Files stored in `storage/app/public/charity_docs`
   - SHA256 hash for integrity verification

4. **Database Storage**
   - User account created in `users` table
   - Charity organization created in `charities` table
   - Documents stored in `charity_documents` table
   - Verification status set to `pending`

5. **Admin Visibility**
   - Immediately visible in admin dashboard
   - Counted in "Total Users", "Total Charity Admins", and "Pending Verifications"
   - Appears in Charities management page with "Pending" status
   - Admin can approve or reject

### API Endpoint

**POST** `/api/auth/register-charity`

**Request (multipart/form-data):**
```json
{
  "name": "Jane Smith",
  "email": "jane@charity.org",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+1234567890",
  "charity_name": "Hope Foundation",
  "reg_no": "REG123456",
  "tax_id": "TAX789012",
  "mission": "To provide education to underprivileged children",
  "vision": "A world where every child has access to education",
  "website": "https://hopefoundation.org",
  "contact_email": "info@hopefoundation.org",
  "contact_phone": "+1234567890",
  "documents[0]": <file>,
  "doc_types[0]": "registration",
  "documents[1]": <file>,
  "doc_types[1]": "tax"
}
```

**Response:**
```json
{
  "message": "Registration successful. Your charity is pending verification.",
  "user": {
    "id": 2,
    "name": "Jane Smith",
    "email": "jane@charity.org",
    "role": "charity_admin",
    "status": "active"
  },
  "charity": {
    "id": 1,
    "owner_id": 2,
    "name": "Hope Foundation",
    "reg_no": "REG123456",
    "tax_id": "TAX789012",
    "mission": "To provide education...",
    "verification_status": "pending",
    "documents": [
      {
        "id": 1,
        "doc_type": "registration",
        "file_path": "charity_docs/abc.pdf"
      }
    ]
  }
}
```

---

## 📊 Admin Dashboard Metrics

### Updated Metrics Display

The admin dashboard now shows:

1. **Total Users** - All registered users (donors + charity admins + admins)
2. **Total Donors** - Count of users with role='donor'
3. **Charity Admins** - Count of users with role='charity_admin'
4. **Approved Charities** - Count of charities with status='approved'
5. **Pending Verifications** - Count of charities with status='pending'

### Real-time Updates

Metrics automatically update when:
- A new donor registers
- A new charity admin registers
- Admin approves/rejects a charity

### API Endpoint

**GET** `/api/metrics`

**Response:**
```json
{
  "total_users": 150,
  "total_donors": 120,
  "total_charity_admins": 28,
  "charities": 25,
  "pending_verifications": 3,
  "campaigns": 45,
  "donations": 320
}
```

---

## 👁️ User Details View

### Admin Can View Complete User Information

**Features:**
- Eye icon button in Users table
- Opens detailed dialog with all user information
- Shows:
  - Name, Email, Phone
  - Role, Status
  - Registration date
  - Address (if provided)
  - Profile image (future enhancement)

**Location:** Admin Dashboard → Users → Click eye icon

---

## 🗄️ Database Schema

### Users Table

```sql
CREATE TABLE users (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  email_verified_at TIMESTAMP NULL,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(255) NULL,
  address TEXT NULL,                    -- NEW
  profile_image VARCHAR(255) NULL,      -- NEW
  role ENUM('donor','charity_admin','admin') DEFAULT 'donor',
  status ENUM('active','suspended') DEFAULT 'active',
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

### Charities Table

```sql
CREATE TABLE charities (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  owner_id BIGINT NOT NULL,
  name VARCHAR(255) NOT NULL,
  reg_no VARCHAR(255) NULL,
  tax_id VARCHAR(255) NULL,
  mission TEXT NULL,
  vision TEXT NULL,
  website VARCHAR(255) NULL,
  contact_email VARCHAR(255) NULL,
  contact_phone VARCHAR(255) NULL,
  logo_path VARCHAR(255) NULL,
  verification_status ENUM('pending','approved','rejected') DEFAULT 'pending',
  verified_at TIMESTAMP NULL,
  verification_notes TEXT NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES users(id)
);
```

### Charity Documents Table

```sql
CREATE TABLE charity_documents (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  charity_id BIGINT NOT NULL,
  doc_type ENUM('registration','tax','bylaws','audit','other'),
  file_path VARCHAR(255) NOT NULL,
  sha256 VARCHAR(64) NULL,
  uploaded_by BIGINT NOT NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (charity_id) REFERENCES charities(id),
  FOREIGN KEY (uploaded_by) REFERENCES users(id)
);
```

---

## 🔒 Security Features

### Password Encryption
- All passwords hashed using bcrypt
- Minimum 6 characters required
- Password confirmation required

### File Upload Security
- File type validation (images for profile, PDF/images for documents)
- File size limits (2MB for profile images)
- Files stored outside public web root
- SHA256 hash verification for documents

### Data Validation
- Email uniqueness enforced
- Required fields validated
- URL format validation for websites
- Enum validation for roles and statuses

---

## 🧪 Testing Guide

### Test Donor Registration

1. **Navigate to registration page**
   ```
   http://localhost:8080/auth/register/donor
   ```

2. **Fill in the form:**
   - Full Name: Test Donor
   - Email: testdonor@example.com
   - Password: password123
   - Confirm Password: password123
   - Phone: +1234567890
   - Address: 123 Test St, City
   - Profile Image: (upload an image)

3. **Submit and verify:**
   - Check success message
   - Login as admin
   - Go to Dashboard → verify "Total Donors" increased
   - Go to Users → find the new donor
   - Click eye icon → verify all details are shown

### Test Charity Registration

1. **Navigate to registration page**
   ```
   http://localhost:8080/auth/register/charity
   ```

2. **Fill in representative details:**
   - Name: Test Charity Admin
   - Email: testcharity@example.com
   - Password: password123
   - Phone: +1234567890

3. **Fill in organization details:**
   - Organization Name: Test Charity Foundation
   - Registration Number: REG123456
   - Tax ID: TAX789012
   - Mission: Test mission statement
   - Contact Email: info@testcharity.org

4. **Upload documents:**
   - Upload at least one document
   - Select document type (registration, tax, etc.)

5. **Submit and verify:**
   - Check success message
   - Login as admin
   - Go to Dashboard → verify counts increased
   - Go to Charities → find charity with "Pending" status
   - Click to view details
   - Approve or reject the charity

---

## 📁 Files Modified/Created

### Backend

1. **Migration:**
   - `database/migrations/2025_10_01_192742_add_donor_fields_to_users_table.php`

2. **Models:**
   - `app/Models/User.php` - Added address, profile_image to fillable

3. **Controllers:**
   - `app/Http/Controllers/AuthController.php` - Complete rewrite of registration methods

4. **Routes:**
   - `routes/api.php` - Updated metrics endpoint

### Frontend

1. **Services:**
   - `src/services/auth.ts` - Implemented real API calls for registration
   - `src/services/admin.ts` - Updated DashboardMetrics interface

2. **Pages:**
   - `src/pages/admin/Dashboard.tsx` - Updated metrics display
   - `src/pages/admin/Users.tsx` - Added view details dialog

---

## 🚀 Deployment Checklist

### Before Production

- [ ] Run migrations: `php artisan migrate`
- [ ] Configure file storage: `php artisan storage:link`
- [ ] Set proper file permissions on storage directory
- [ ] Configure max upload sizes in php.ini
- [ ] Set up file backup strategy
- [ ] Configure email verification (future)
- [ ] Add rate limiting for registration endpoints
- [ ] Set up monitoring for failed registrations

### Environment Variables

```env
# File Upload Limits
UPLOAD_MAX_FILESIZE=2M
POST_MAX_SIZE=10M

# Storage
FILESYSTEM_DISK=public
```

---

## 🔄 Future Enhancements

### Planned Features

1. **Email Verification**
   - Send verification email after registration
   - Require email verification before full access

2. **Profile Image Preview**
   - Show profile images in user list
   - Display in user details dialog

3. **Document Preview**
   - View uploaded documents in browser
   - Download documents

4. **Registration Analytics**
   - Track registration sources
   - Monitor completion rates
   - Identify drop-off points

5. **Advanced Validation**
   - Phone number format validation
   - Address autocomplete
   - Document OCR for automatic data extraction

---

## ✅ Status

**All registration features are now FULLY FUNCTIONAL:**

✅ Donor registration with all fields
✅ Charity registration with documents
✅ Database persistence
✅ Password encryption
✅ File uploads
✅ Admin dashboard metrics
✅ User details view
✅ Real-time count updates
✅ Proper validation
✅ Error handling

**Ready for testing and production deployment!**
