# Terms of Service and Privacy Policy Pages Added ✓

## What Was Created

I've created separate Terms of Service and Privacy Policy pages for both Donors and Charities, with all the content you provided, properly formatted without numbers.

### Files Created:

1. **`src/pages/legal/DonorTerms.tsx`** - Terms of Service for Donors
2. **`src/pages/legal/DonorPrivacy.tsx`** - Privacy Policy for Donors
3. **`src/pages/legal/CharityTerms.tsx`** - Terms of Service for Charitable Organizations
4. **`src/pages/legal/CharityPrivacy.tsx`** - Privacy Policy for Charitable Organizations

## Formatting Changes Applied

As you requested, I removed all the numbers (1., 2., 3., etc.) and made the section titles **bold** using `<h2>` tags:

### Before (with numbers):
```
1. Acceptance of Terms
2. The Platform's Role
3. Your Responsibilities as a Donor
```

### After (bold titles, no numbers):
```
**Acceptance of Terms**
**The Platform's Role**
**Your Responsibilities as a Donor**
```

## Routes Added

Added the following routes to `App.tsx`:

```tsx
{/* Legal Routes */}
<Route path="/legal/donor/terms" element={<DonorTerms />} />
<Route path="/legal/donor/privacy" element={<DonorPrivacy />} />
<Route path="/legal/charity/terms" element={<CharityTerms />} />
<Route path="/legal/charity/privacy" element={<CharityPrivacy />} />
```

## Registration Pages Updated

### Donor Registration (`RegisterDonor.tsx`):
- Links now point to `/legal/donor/terms` and `/legal/donor/privacy`
- Added `target="_blank"` to open in new tab

### Charity Registration (`RegisterCharity.tsx`):
- Links now point to `/legal/charity/terms` and `/legal/charity/privacy`
- Added `target="_blank"` to open in new tab

## Page Features

Each legal page includes:

✅ **Clean, professional design** with gradient background
✅ **Card layout** with shadow for better readability
✅ **Back button** to return to registration
✅ **Centered header** with title and effective date
✅ **Disclaimer** at the top (italicized)
✅ **Bold section titles** (no numbers)
✅ **Proper spacing** between sections
✅ **Responsive design** - works on all screen sizes
✅ **Consistent styling** with the rest of the app

## Content Included

### Donor Documents:
- **Terms of Service**: Covers acceptance, platform role, responsibilities, user conduct, liability, governing law
- **Privacy Policy**: Covers data collection, usage, sharing, security, user rights, contact info

### Charity Documents:
- **Terms of Service**: Covers acceptance, platform role, charity obligations, user conduct, platform rights, governing law
- **Privacy Policy**: Covers data collection (including verification documents), usage, sharing, security, rights, contact info

## How to Access

### For Donors:
1. Go to donor registration: `http://localhost:8081/auth/register/donor`
2. Click on "Terms of Service" or "Privacy Policy" links
3. Pages open in new tab

### For Charities:
1. Go to charity registration: `http://localhost:8081/auth/register/charity`
2. Click on "Terms of Service" or "Privacy Policy" links (in Step 4: Review & Submit)
3. Pages open in new tab

## Direct URLs:

- Donor Terms: `http://localhost:8081/legal/donor/terms`
- Donor Privacy: `http://localhost:8081/legal/donor/privacy`
- Charity Terms: `http://localhost:8081/legal/charity/terms`
- Charity Privacy: `http://localhost:8081/legal/charity/privacy`

## Design Highlights

- **Typography**: Large, bold headings (`text-2xl font-bold`) for section titles
- **Spacing**: Generous spacing (`space-y-6`) between sections for readability
- **Lists**: Bullet points with proper indentation for easy scanning
- **Emphasis**: Important points use `<strong>` tags
- **Professional**: Clean card design with proper padding and shadows

All content is now visible and properly formatted! 🎉
