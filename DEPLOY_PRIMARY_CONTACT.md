# Quick Deploy Guide - Primary Contact Update

## 🚀 Run These Commands

### 1. Backend - Run Migration
```bash
cd capstone_backend
php artisan migrate
```

### 2. Verify Database
```bash
php artisan db:show
```

### 3. Test Registration Endpoint (Optional)
```bash
php artisan tinker
```
Then in tinker:
```php
$charity = new App\Models\Charity();
dd($charity->getFillable());
```

You should see the new fields in the fillable array.

---

## ✅ Verification Checklist

### Database
- [ ] Open MySQL Workbench
- [ ] Select `capstone_db` database
- [ ] Run: `DESCRIBE charities;`
- [ ] Verify these columns exist:
  - `primary_first_name`
  - `primary_middle_initial`
  - `primary_last_name`
  - `primary_position`
  - `primary_email`
  - `primary_phone`
  - `primary_alt_phone`
  - `preferred_contact_method`

### Frontend
- [ ] Navigate to: `http://localhost:8080/register-charity`
- [ ] Scroll to "Primary Contact" section
- [ ] Verify you see:
  - [ ] Three name fields (First, M.I., Last) in one row
  - [ ] Position field
  - [ ] Email and Phone in one row
  - [ ] Alternate Phone and Preferred Method in one row

### Test Validation
- [ ] Try entering "123" in First Name → Should be blocked
- [ ] Try entering "AB" in M.I. → Should only keep "A"
- [ ] Try entering "abc" in Phone → Should be blocked
- [ ] Try entering "1234567890" in Phone → Should show error (needs 11 digits)
- [ ] Enter "09171234567" in Phone → Should be accepted
- [ ] Leave First Name empty and click Next → Should show error

---

## 🔄 If You Need to Rollback

```bash
cd capstone_backend
php artisan migrate:rollback --step=1
```

This will remove the new columns and restore the old structure.

---

## 📊 Expected Database State

### Old Columns (Still Present)
```
contact_person_name
contact_email  
contact_phone
```

### New Columns (Added)
```
primary_first_name
primary_middle_initial
primary_last_name
primary_position
primary_email
primary_phone
primary_alt_phone
preferred_contact_method
```

**Note**: Old columns are kept for backward compatibility. You can remove them later after confirming everything works.

---

## 🎯 Test Data Example

Try registering with this data:

**Primary Contact**:
- First Name: `Maria`
- M.I.: `C`
- Last Name: `Santos`
- Position: `Executive Director`
- Email: `maria.santos@helpinghandsph.org`
- Phone: `09171234567`
- Alt Phone: `09281234567`
- Preferred Method: `Both`

Should save successfully with all fields properly stored in database.

---

## 🆘 Troubleshooting

### Error: "Column already exists"
```bash
php artisan migrate:rollback --step=1
php artisan migrate
```

### Error: "Class not found"
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Frontend not showing new fields
1. Hard refresh: `Ctrl + Shift + R`
2. Check browser console for errors
3. Verify component file exists: `src/components/forms/PrimaryContactFields.tsx`

---

## ✨ You're Done!

The Primary Contact section is now fully implemented and ready for production use!
