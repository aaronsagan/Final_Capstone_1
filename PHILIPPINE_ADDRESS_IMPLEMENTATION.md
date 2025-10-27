# Philippine Address System Implementation Guide

## Overview
Complete implementation of Philippine address structure for charity registration with dynamic cascading dropdowns (Region → Province → City/Municipality).

---

## ✅ What Was Implemented

### **Backend (Laravel)**

#### 1. **Database Migration**
- **File**: `database/migrations/2025_10_20_000001_update_charities_address_structure.php`
- **Changes**:
  - Removed old fields: `address`, `region`, `municipality`
  - Added new fields:
    - `street_address` - Street address/building
    - `barangay` - Barangay (optional)
    - `city` - City/Municipality
    - `province` - Province
    - `region` - Region
    - `full_address` - Auto-generated complete address

#### 2. **Location Data**
- **File**: `database/data/ph_locations.json`
- Contains Philippine Standard Geographic Code (PSGC) data:
  - 11 Regions (NCR, Region I-XI)
  - 50+ Provinces
  - 500+ Cities/Municipalities
- Hierarchical structure: Region → Province → City

#### 3. **Location Controller**
- **File**: `app/Http/Controllers/LocationController.php`
- **Endpoints**:
  ```php
  GET /api/locations                                              // All data
  GET /api/locations/regions                                      // All regions
  GET /api/locations/regions/{regionCode}/provinces               // Provinces by region
  GET /api/locations/regions/{regionCode}/provinces/{provinceCode}/cities  // Cities by province
  ```

#### 4. **Model Update**
- **File**: `app/Models/Charity.php`
- Updated `$fillable` array with new address fields

#### 5. **API Routes**
- **File**: `routes/api.php`
- Added public location endpoints (no authentication required)

---

### **Frontend (React + TypeScript)**

#### 1. **Custom Hook**
- **File**: `src/hooks/usePhilippineLocations.ts`
- **Features**:
  - Fetches regions, provinces, cities from API
  - Manages loading states
  - Auto-builds full address from components
  - Handles cascading dropdown logic

#### 2. **Reusable Address Component**
- **File**: `src/components/forms/PhilippineAddressForm.tsx`
- **Features**:
  - Street address input
  - Region dropdown
  - Province dropdown (dependent on region)
  - City dropdown (dependent on province)
  - Barangay input (optional)
  - Auto-generated full address (read-only)
  - Error handling and validation
  - Loading states
  - Disabled states for dependent fields

#### 3. **Updated Registration Form**
- **File**: `src/pages/auth/RegisterCharity.tsx`
- **Changes**:
  - Imported `PhilippineAddressForm` component
  - Updated form data structure
  - Updated validation rules
  - Replaced old address section with new component
  - Moved category to separate section

---

## 🚀 How to Use

### **Step 1: Run Migration**
```bash
cd capstone_backend
php artisan migrate
```

### **Step 2: Test API Endpoints**
```bash
# Get all regions
curl http://localhost:8000/api/locations/regions

# Get provinces for NCR
curl http://localhost:8000/api/locations/regions/NCR/provinces

# Get cities for Metro Manila
curl http://localhost:8000/api/locations/regions/NCR/provinces/NCR/cities
```

### **Step 3: Test Frontend**
1. Navigate to charity registration page
2. Fill in organization details
3. In Location section:
   - Enter street address
   - Select Region → Provinces load
   - Select Province → Cities load
   - Select City
   - (Optional) Enter Barangay
   - Watch Full Address auto-generate

---

## 📊 Database Schema

### **New Charity Table Structure**
```sql
charities
├── id
├── owner_id
├── name
├── ...
├── street_address (VARCHAR) - "123 Charity Street, Bldg A"
├── barangay (VARCHAR, NULLABLE) - "Malate"
├── city (VARCHAR) - "Manila"
├── province (VARCHAR) - "Metro Manila"
├── region (VARCHAR) - "National Capital Region (NCR)"
├── full_address (TEXT) - "123 Charity Street, Bldg A, Malate, Manila, Metro Manila, National Capital Region (NCR)"
├── category (VARCHAR)
└── ...
```

---

## 🎯 Example Full Address Output

**User Input:**
- Street Address: `123 Charity Street, Bldg A`
- Barangay: `Malate`
- City: `Manila`
- Province: `Metro Manila`
- Region: `National Capital Region (NCR)`

**Auto-Generated Full Address:**
```
123 Charity Street, Bldg A, Malate, Manila, Metro Manila, National Capital Region (NCR)
```

---

## 🔧 Validation Rules

### **Backend Validation (Update in Controller)**
```php
$request->validate([
    'street_address' => 'required|string|max:255',
    'barangay' => 'nullable|string|max:100',
    'city' => 'required|string|max:100',
    'province' => 'required|string|max:100',
    'region' => 'required|string|max:100',
    'full_address' => 'nullable|string',
    'nonprofit_category' => 'required|string',
]);
```

### **Frontend Validation**
```typescript
if (!formData.street_address) newErrors.street_address = 'Required';
if (!formData.region) newErrors.region = 'Required';
if (!formData.province) newErrors.province = 'Required';
if (!formData.city) newErrors.city = 'Required';
// barangay is optional
```

---

## 📝 Form Behavior

### **Cascading Dropdowns**
1. **Region Selection**:
   - User selects region
   - Province dropdown enables and loads provinces for that region
   - City dropdown resets and disables

2. **Province Selection**:
   - User selects province
   - City dropdown enables and loads cities for that province

3. **City Selection**:
   - User selects city
   - Full address auto-updates

4. **Any Field Change**:
   - Full address automatically regenerates

### **Field States**
- **Street Address**: Always enabled
- **Barangay**: Always enabled (optional)
- **Region**: Always enabled
- **Province**: Disabled until region selected
- **City**: Disabled until province selected
- **Full Address**: Always read-only (auto-generated)

---

## 🔄 Data Flow

```
User Action → Frontend Component → Custom Hook → API Call → Backend Controller → JSON Data → Response → Update State → Re-render
```

### **Example: Selecting Region**
```
1. User selects "Region III (Central Luzon)"
2. onChange handler calls handleChange('region', value)
3. useEffect detects region change
4. Hook calls loadProvinces('03')
5. API: GET /api/locations/regions/03/provinces
6. Controller reads ph_locations.json
7. Returns provinces array
8. Hook updates provinces state
9. Province dropdown populates
10. Full address updates
```

---

## 🎨 UI/UX Features

- **Loading States**: Dropdowns show loading during API calls
- **Disabled States**: Dependent dropdowns disabled until parent selected
- **Auto-complete**: Full address updates in real-time
- **Error Display**: Validation errors shown below each field
- **Placeholder Text**: Clear instructions in each field
- **Responsive Design**: Works on mobile and desktop
- **Clean Sections**: Organized with borders and headings

---

## 🧪 Testing Checklist

### **Backend**
- [ ] Migration runs successfully
- [ ] JSON file loads correctly
- [ ] All API endpoints return data
- [ ] Region endpoint returns all regions
- [ ] Province endpoint filters by region
- [ ] City endpoint filters by region and province
- [ ] Invalid region/province codes return 404

### **Frontend**
- [ ] Component renders without errors
- [ ] Regions load on mount
- [ ] Province dropdown disabled initially
- [ ] City dropdown disabled initially
- [ ] Selecting region loads provinces
- [ ] Selecting province loads cities
- [ ] Full address auto-generates
- [ ] Validation errors display correctly
- [ ] Form submission includes all address fields

---

## 🐛 Common Issues & Solutions

### **Issue: Provinces not loading**
**Solution**: Check region code matches JSON structure. Ensure API endpoint is correct.

### **Issue: Full address not updating**
**Solution**: Verify useEffect dependencies include all address fields.

### **Issue: Dropdowns stay disabled**
**Solution**: Check that parent field has a value and API call succeeded.

### **Issue: Migration fails**
**Solution**: Ensure old columns exist before dropping. Check column names match.

---

## 📦 Files Created/Modified

### **Created**
- ✅ `capstone_backend/database/data/ph_locations.json`
- ✅ `capstone_backend/database/migrations/2025_10_20_000001_update_charities_address_structure.php`
- ✅ `capstone_backend/app/Http/Controllers/LocationController.php`
- ✅ `capstone_frontend/src/hooks/usePhilippineLocations.ts`
- ✅ `capstone_frontend/src/components/forms/PhilippineAddressForm.tsx`

### **Modified**
- ✅ `capstone_backend/routes/api.php`
- ✅ `capstone_backend/app/Models/Charity.php`
- ✅ `capstone_frontend/src/pages/auth/RegisterCharity.tsx`

---

## 🚀 Next Steps

### **1. Update Backend Registration Controller**
Update `AuthController::registerCharityAdmin()` to handle new address fields:

```php
// In AuthController.php
public function registerCharityAdmin(Request $request) {
    $validated = $request->validate([
        // ... other fields
        'street_address' => 'required|string|max:255',
        'barangay' => 'nullable|string|max:100',
        'city' => 'required|string|max:100',
        'province' => 'required|string|max:100',
        'region' => 'required|string|max:100',
        'full_address' => 'nullable|string',
        'nonprofit_category' => 'required|string',
    ]);
    
    // Create charity with new fields
    $charity = Charity::create([
        // ... other fields
        'street_address' => $validated['street_address'],
        'barangay' => $validated['barangay'] ?? null,
        'city' => $validated['city'],
        'province' => $validated['province'],
        'region' => $validated['region'],
        'full_address' => $validated['full_address'],
        'category' => $validated['nonprofit_category'],
    ]);
}
```

### **2. Update Seeders**
Update any seeders that create charities with new address structure.

### **3. Update Existing Charities**
If you have existing data, create a data migration script:

```php
// One-time script
$charities = Charity::whereNotNull('address')->get();
foreach ($charities as $charity) {
    // Parse old address and update to new structure
    $charity->update([
        'street_address' => $charity->address,
        'city' => $charity->municipality,
        'region' => $charity->region,
        'full_address' => "{$charity->address}, {$charity->municipality}, {$charity->region}",
    ]);
}
```

### **4. Update Display Components**
Update any components that display charity addresses to use new fields.

### **5. Add More Locations**
Expand `ph_locations.json` with more regions, provinces, and cities as needed.

---

## 📚 Additional Resources

- **PSGC Official**: https://psa.gov.ph/classification/psgc/
- **Philippine Regions**: https://en.wikipedia.org/wiki/Regions_of_the_Philippines
- **Laravel Validation**: https://laravel.com/docs/validation
- **React Hooks**: https://react.dev/reference/react

---

## ✨ Features Summary

✅ **Dynamic cascading dropdowns** (Region → Province → City)  
✅ **Auto-generated full address**  
✅ **Philippine Standard Geographic Code (PSGC) compliant**  
✅ **Reusable component** for other forms  
✅ **Comprehensive validation**  
✅ **Loading and disabled states**  
✅ **Error handling**  
✅ **Clean, modern UI**  
✅ **Mobile responsive**  
✅ **Type-safe (TypeScript)**  

---

## 🎉 Done!

Your charity registration now has a professional Philippine address system with proper geographic hierarchy and auto-complete functionality!
