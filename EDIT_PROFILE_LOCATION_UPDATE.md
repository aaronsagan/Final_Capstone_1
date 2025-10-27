# Edit Profile - Location Information Update ✅

## Changes Made

Updated the Location Information section in Edit Profile to match the Charity Registration form layout.

### 1. **Frontend Changes**

#### Updated Form Structure
- **File**: `capstone_frontend/src/pages/charity/EditProfile.tsx`

**Before:**
- Simple fields: Region, Municipality, Address Line
- Manual dropdown with limited locations data

**After:**
- **Street Address / Building** - Text input (required)
- **Region** - Dropdown with all Philippine regions (required)
- **Province** - Auto-filtered dropdown based on region (required)
- **City / Municipality** - Auto-filtered dropdown based on province (required)
- **Barangay** - Text input (optional)
- **Full Address** - Auto-generated, read-only field

#### Using PhilippineAddressForm Component
Now uses the same component as registration:
```tsx
<PhilippineAddressForm
  values={{
    street_address: formData.street_address,
    barangay: formData.barangay,
    city: formData.city,
    province: formData.province,
    region: formData.region,
    full_address: formData.full_address,
  }}
  errors={errors}
  onChange={handleInputChange}
/>
```

#### Updated Form Data Structure
```typescript
const [formData, setFormData] = useState({
  mission: "",
  vision: "",
  description: "",
  street_address: "",    // NEW
  barangay: "",          // NEW
  city: "",              // NEW
  province: "",          // NEW
  region: "",
  full_address: "",      // NEW (auto-generated)
  first_name: "",
  middle_initial: "",
  last_name: "",
  contact_email: "",
  contact_phone: "",
});
```

#### Data Loading with Fallbacks
```typescript
setFormData({
  // ... other fields
  street_address: charity.street_address || charity.address || "",
  barangay: charity.barangay || "",
  city: charity.city || charity.municipality || "",
  province: charity.province || "",
  region: charity.region || "",
  full_address: charity.full_address || "",
  // ... other fields
});
```

### 2. **Backend Changes**

#### Updated Validation Rules
- **File**: `capstone_backend/app/Http/Controllers/CharityController.php`

```php
$validated = $r->validate([
    'mission' => 'required|string|min:30',
    'vision' => 'nullable|string',
    'description' => 'required|string|min:50|max:500',
    'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    'cover_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    'street_address' => 'required|string',      // NEW
    'barangay' => 'nullable|string',            // NEW
    'city' => 'required|string',                // NEW
    'province' => 'required|string',            // NEW
    'region' => 'required|string',
    'full_address' => 'nullable|string',        // NEW
    'first_name' => 'required|string|max:50',
    'middle_initial' => 'nullable|string|max:1',
    'last_name' => 'required|string|max:50',
    'contact_email' => 'required|email|unique:charities,contact_email,' . $charity->id,
    'contact_phone' => ['required', 'regex:/^(09|\+639)\d{9}$/']
]);
```

#### Model Already Supports All Fields
The `Charity` model already has all needed fields in the `$fillable` array:
- `street_address`
- `barangay`
- `city`
- `province`
- `region`
- `full_address`

### 3. **Features**

#### Auto-Filtering Dropdowns
✅ **Region Selection** → Loads provinces for that region
✅ **Province Selection** → Loads cities for that province
✅ **City Selection** → Updates full address

#### Auto-Generated Full Address
The full address is automatically built from:
```
[Street Address], [Barangay], [City], [Province], [Region]
```

Example:
```
Input:
- Street Address: 123 Charity Street, Bldg A
- Barangay: Malate
- City: Manila
- Province: Metro Manila
- Region: National Capital Region (NCR)

Auto-generated Full Address:
123 Charity Street, Bldg A, Malate, Manila, Metro Manila, National Capital Region (NCR)
```

#### Pre-filled Data
All location fields are pre-filled with existing charity data:
- Shows current street address
- Shows current barangay
- Shows current city/municipality
- Shows current province
- Shows current region
- Shows auto-generated full address

#### Dropdown Updates
When charity clicks on a dropdown:
- **Region dropdown** → Shows all Philippine regions
- **Province dropdown** → Shows provinces in selected region (disabled until region selected)
- **City dropdown** → Shows cities in selected province (disabled until province selected)

### 4. **User Experience**

#### Viewing Existing Data
1. Page loads with all fields pre-filled
2. Dropdowns show current selections
3. Full address is displayed (read-only)

#### Updating Location
1. Change region → Province and city reset, new provinces load
2. Change province → City resets, new cities load
3. Change city → Full address updates automatically
4. Change street address or barangay → Full address updates automatically

#### Visual Layout
Matches registration form exactly:
```
┌─────────────────────────────────────────────────┐
│ Street Address / Building *                     │
│ [e.g., 123 Charity Street, Bldg A]             │
└─────────────────────────────────────────────────┘

┌────────────────────────┐ ┌────────────────────────┐
│ Region *               │ │ Province *             │
│ [Select Region      ▼] │ │ [Select Province   ▼] │
└────────────────────────┘ └────────────────────────┘

┌────────────────────────┐ ┌────────────────────────┐
│ City / Municipality *  │ │ Barangay (Optional)    │
│ [Select City/Muni.. ▼] │ │ [e.g., Malate]        │
└────────────────────────┘ └────────────────────────┘

┌─────────────────────────────────────────────────┐
│ Full Address (Auto-generated)                   │
│ [Address will be generated automatically...]    │
│ ℹ️ This field is automatically filled based on  │
│   your selections above.                        │
└─────────────────────────────────────────────────┘
```

### 5. **Benefits**

✅ **Consistency** - Same layout as registration form
✅ **Better UX** - Cascading dropdowns prevent invalid selections
✅ **Data Quality** - Structured address data
✅ **Auto-completion** - Full address generated automatically
✅ **Pre-filled** - Shows existing data
✅ **Easy Updates** - Just select from dropdowns
✅ **Validation** - Ensures all required fields are filled

### 6. **Technical Details**

#### Component Used
- `PhilippineAddressForm` from `@/components/forms/PhilippineAddressForm`
- Uses `usePhilippineLocations` hook for data loading
- Handles cascading dropdown logic automatically

#### Data Flow
1. Load charity profile from `/api/me`
2. Extract location fields (with fallbacks for legacy field names)
3. Pre-fill form with existing data
4. PhilippineAddressForm handles dropdown filtering
5. Full address auto-updates on any change
6. On save, send all location fields to backend
7. Backend validates and saves to database

#### Backward Compatibility
The code handles both old and new field names:
- `address` → `street_address`
- `municipality` → `city`
- Tries both when loading existing data

### 7. **Files Modified**

1. **Frontend**:
   - `capstone_frontend/src/pages/charity/EditProfile.tsx`
     - Imported `PhilippineAddressForm` component
     - Updated form data structure
     - Updated data loading logic
     - Replaced custom location fields with component
     - Updated validation

2. **Backend**:
   - `capstone_backend/app/Http/Controllers/CharityController.php`
     - Updated validation rules in `updateProfile()` method

3. **Database**:
   - No migration needed - fields already exist from previous migrations

## Summary

The Location Information section in Edit Profile now:
- ✅ **Matches registration form layout** exactly
- ✅ **Uses cascading dropdowns** (Region → Province → City)
- ✅ **Auto-generates full address**
- ✅ **Pre-fills existing data**
- ✅ **Provides better user experience**
- ✅ **Ensures data quality** with structured fields

Users can now easily update their location by selecting from dropdowns, just like during registration! 🎉
