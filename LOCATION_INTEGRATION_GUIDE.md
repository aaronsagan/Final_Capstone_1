# 🚀 Quick Integration Guide - LocationSelector

## How to Add LocationSelector to Your Forms (5 Minutes Per Form)

---

## Template Code - Copy & Paste

### 1. Import Statement (Add to top of file)
```tsx
import LocationSelector, { LocationData } from '@/components/LocationSelector';
```

### 2. State Management (Add to component)
```tsx
// Add location state
const [location, setLocation] = useState<LocationData>({
  region: '',
  province: '',
  city: '',
  barangay: ''
});

// Add location errors state
const [locationErrors, setLocationErrors] = useState<{
  region?: string;
  province?: string;
  city?: string;
  barangay?: string;
}>({});
```

### 3. Component in JSX (Replace address inputs)
```tsx
<LocationSelector
  value={location}
  onChange={setLocation}
  required={true}
  errors={locationErrors}
/>
```

### 4. Validation Before Submit
```tsx
const validateLocation = () => {
  const errors: any = {};
  
  if (!location.region) errors.region = 'Region is required';
  if (!location.province) errors.province = 'Province is required';
  if (!location.city) errors.city = 'City/Municipality is required';
  if (!location.barangay) errors.barangay = 'Barangay is required';
  
  setLocationErrors(errors);
  
  return Object.keys(errors).length === 0;
};

const handleSubmit = async (e: React.FormEvent) => {
  e.preventDefault();
  
  // Validate location
  if (!validateLocation()) {
    toast.error('Please complete your full address including barangay');
    return;
  }
  
  // Continue with form submission...
};
```

### 5. Include in Form Data
```tsx
const formData = new FormData();

// Add other fields...
formData.append('region', location.region);
formData.append('province', location.province);
formData.append('city', location.city);
formData.append('barangay', location.barangay);

// Or for JSON:
const jsonData = {
  // ... other fields
  region: location.region,
  province: location.province,
  city: location.city,
  barangay: location.barangay
};
```

---

## Form-Specific Instructions

### 🏢 Charity Registration Form

**Find:** Address input fields or text areas  
**Replace with:** LocationSelector component

**Additional Field:** Keep `street_address` as separate text input if needed:
```tsx
<div className="space-y-2">
  <Label htmlFor="street_address">Street Address (Optional)</Label>
  <Input
    id="street_address"
    type="text"
    placeholder="Building number, street name"
    value={streetAddress}
    onChange={(e) => setStreetAddress(e.target.value)}
  />
</div>

<LocationSelector
  value={location}
  onChange={setLocation}
  required={true}
  errors={locationErrors}
/>
```

**Submit Example:**
```tsx
const submitCharity = async () => {
  const formData = new FormData();
  
  // ... other charity fields
  formData.append('street_address', streetAddress);
  formData.append('region', location.region);
  formData.append('province', location.province);
  formData.append('city', location.city);
  formData.append('barangay', location.barangay);
  
  const response = await fetch('/api/auth/register-charity', {
    method: 'POST',
    body: formData
  });
};
```

---

### 👤 Donor Registration Form

**Find:** Address text field  
**Replace with:** LocationSelector component

**Before:**
```tsx
<Input
  type="text"
  placeholder="Full address"
  value={address}
  onChange={(e) => setAddress(e.target.value)}
/>
```

**After:**
```tsx
<div className="space-y-2">
  <Label htmlFor="address">Street Address (Optional)</Label>
  <Input
    id="address"
    type="text"
    placeholder="Building number, street name"
    value={address}
    onChange={(e) => setAddress(e.target.value)}
  />
</div>

<LocationSelector
  value={location}
  onChange={setLocation}
  required={true}
  errors={locationErrors}
/>
```

**Submit Example:**
```tsx
const submitDonor = async () => {
  const formData = new FormData();
  
  formData.append('name', name);
  formData.append('email', email);
  formData.append('password', password);
  formData.append('password_confirmation', passwordConfirmation);
  formData.append('address', address); // Street address
  formData.append('region', location.region);
  formData.append('province', location.province);
  formData.append('city', location.city);
  formData.append('barangay', location.barangay);
  
  const response = await fetch('/api/auth/register', {
    method: 'POST',
    body: formData
  });
};
```

---

### 📢 Campaign Creation Form

**Find:** Region, Province, City dropdowns or inputs  
**Replace with:** LocationSelector component (adds Barangay)

**Before:**
```tsx
<Input placeholder="Region" value={region} onChange={...} />
<Input placeholder="Province" value={province} onChange={...} />
<Input placeholder="City" value={city} onChange={...} />
```

**After:**
```tsx
<LocationSelector
  value={location}
  onChange={setLocation}
  required={true}
  errors={locationErrors}
/>
```

**Submit Example:**
```tsx
const submitCampaign = async () => {
  const campaignData = {
    title: title,
    description: description,
    beneficiary_category: selectedCategories,
    region: location.region,
    province: location.province,
    city: location.city,
    barangay: location.barangay,
    target_amount: targetAmount,
    // ... other fields
  };
  
  const response = await fetch(`/api/charities/${charityId}/campaigns`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify(campaignData)
  });
};
```

---

## Error Handling

### Handle API Validation Errors
```tsx
try {
  const response = await fetch('/api/endpoint', {
    method: 'POST',
    body: formData
  });
  
  if (!response.ok) {
    const error = await response.json();
    
    // Extract location errors from API response
    if (error.errors) {
      setLocationErrors({
        region: error.errors.region?.[0],
        province: error.errors.province?.[0],
        city: error.errors.city?.[0],
        barangay: error.errors.barangay?.[0]
      });
    }
    
    toast.error(error.message || 'Validation failed');
    return;
  }
  
  toast.success('Success!');
} catch (err) {
  toast.error('An error occurred');
}
```

---

## Testing Checklist

For each form you integrate, test:

### ✅ Required Field Validation
- [ ] Submit without selecting region → Should show error
- [ ] Submit without selecting province → Should show error
- [ ] Submit without selecting city → Should show error
- [ ] Submit without selecting barangay → Should show error

### ✅ Cascading Behavior
- [ ] Select region → Province dropdown enables
- [ ] Select province → City dropdown enables
- [ ] Select city → Barangay dropdown enables
- [ ] Change region → Province/City/Barangay reset
- [ ] Change province → City/Barangay reset
- [ ] Change city → Barangay resets

### ✅ Success Flow
- [ ] Fill all 4 fields → ✓ Address complete shows
- [ ] Submit form → Success
- [ ] Check database → All 4 fields saved
- [ ] Check API response → All 4 fields returned

### ✅ Visual Verification
- [ ] Loading states show (if using API)
- [ ] Error messages display correctly
- [ ] Completion checkmark appears
- [ ] Dropdowns are disabled appropriately
- [ ] Mobile responsive (test on small screen)

---

## Common Issues & Solutions

### Issue: "Cannot read property 'map' of undefined"
**Cause:** Location data not loaded  
**Solution:** Check import path for `philippineLocations.ts`

### Issue: Dropdown not enabling after selection
**Cause:** State not updating  
**Solution:** Ensure you're using `setLocation` correctly

### Issue: Form submits with empty location
**Cause:** No validation before submit  
**Solution:** Add `validateLocation()` check in submit handler

### Issue: API returns 422 validation error
**Cause:** Backend expects different field names  
**Solution:** Check API expects: `region`, `province`, `city`, `barangay`

---

## Quick Reference

### Component Props
```tsx
<LocationSelector
  value={location}           // Required: LocationData object
  onChange={setLocation}     // Required: Update function
  required={true}            // Optional: Show asterisks
  disabled={false}           // Optional: Disable all dropdowns
  errors={locationErrors}    // Optional: Show validation errors
/>
```

### Location Data Structure
```typescript
interface LocationData {
  region: string;
  province: string;
  city: string;
  barangay: string;
}
```

### API Fields Expected
```json
{
  "region": "Region IV-A - CALABARZON",
  "province": "Cavite",
  "city": "Dasmariñas City",
  "barangay": "Langkaan I"
}
```

---

## 🎯 That's It!

Follow these steps for each form:
1. Import component
2. Add state
3. Add component to JSX
4. Validate before submit
5. Include in form data
6. Test!

**Time per form: ~5-10 minutes**

Good luck! 🚀
