# Beneficiary & Location Implementation for Campaigns

## Overview
Added optional beneficiary text and structured Philippine location fields to campaigns for richer analytics and targeting.

## Backend Changes

### 1. Database Migration
**File**: `database/migrations/2025_01_24_000001_add_beneficiary_and_location_to_campaigns_table.php`

Added fields to campaigns table:
- `beneficiary` (text, nullable) - Who benefits from this campaign
- `region` (string, nullable) - Philippine region
- `province` (string, nullable) - Philippine province
- `city` (string, nullable) - City/Municipality
- `barangay` (string, nullable) - Barangay

**To run migration:**
```bash
cd capstone_backend
php artisan migrate
```

### 2. Campaign Model
**File**: `app/Models/Campaign.php`

Added to `$fillable` array:
- beneficiary
- region
- province
- city
- barangay

### 3. Campaign Controller
**File**: `app/Http/Controllers/CampaignController.php`

Updated validation in both `store()` and `update()` methods:
```php
'beneficiary' => 'nullable|string|max:1000',
'region' => 'nullable|string|max:255',
'province' => 'nullable|string|max:255',
'city' => 'nullable|string|max:255',
'barangay' => 'nullable|string|max:255',
```

## Frontend Changes

### 1. Create Campaign Modal
**File**: `components/charity/CreateCampaignModal.tsx`

**Imported:**
- `PhilippineAddressForm` component (reused from charity registration)
- `MapPin` icon from lucide-react

**Added to form state:**
- beneficiary
- street_address
- barangay
- city
- province
- region
- full_address

**New UI sections:**

#### Beneficiary Field
```tsx
<Label htmlFor="cc-beneficiary">Beneficiaries (Optional)</Label>
<Textarea 
  id="cc-beneficiary" 
  rows={3} 
  value={form.beneficiary} 
  onChange={(e) => setForm({ ...form, beneficiary: e.target.value })} 
  placeholder="Who will benefit from this campaign?"
  maxLength={1000}
/>
```

#### Location Form
Reused `PhilippineAddressForm` component with cascading dropdowns:
- Region selector
- Province selector (loads based on region)
- City/Municipality selector (loads based on province)
- Barangay text input
- Auto-generated full address display

**Positioned:** After "Expected Outcome" and before "Financials & Media"

### 2. Philippine Address Form Component
**File**: `components/forms/PhilippineAddressForm.tsx` (existing, reused)

Features:
- Cascading location selectors using Philippine PSGC data
- Auto-generates full address
- Validates location hierarchy
- Responsive grid layout

## Field Details

### Beneficiary Field
- **Type**: Text area
- **Max Length**: 1000 characters
- **Optional**: Yes
- **Purpose**: Describe who benefits from the campaign
- **Examples**: 
  - "500 students in rural communities"
  - "Families affected by typhoon Odette"
  - "Malnourished children aged 5-12 in Metro Manila"

### Location Fields
All location fields are **optional** and follow Philippine PSGC structure:

| Field | Type | Description |
|-------|------|-------------|
| `region` | String | Philippine region name |
| `province` | String | Province name |
| `city` | String | City or municipality name |
| `barangay` | String | Barangay name |
| `street_address` | String | Street/building (not saved to DB) |
| `full_address` | String | Auto-generated (not saved to DB) |

**Note**: Only region, province, city, and barangay are saved to the database for analytics.

## Verification Steps

1. **Run the migration:**
   ```bash
   cd capstone_backend
   php artisan migrate
   ```

2. **Create a test campaign:**
   - Log in as charity admin
   - Click "Create Campaign"
   - Fill in required fields
   - Scroll to "Beneficiary & Location" section
   - Enter beneficiary description
   - Select location using dropdowns
   - Submit the form

3. **Verify in database:**
   ```sql
   SELECT id, title, beneficiary, region, province, city, barangay 
   FROM campaigns 
   ORDER BY id DESC 
   LIMIT 5;
   ```

4. **Check API response:**
   ```bash
   GET /api/campaigns/{id}
   ```
   Should include:
   ```json
   {
     "id": 123,
     "beneficiary": "500 students in rural communities",
     "region": "National Capital Region",
     "province": "Metro Manila",
     "city": "Manila",
     "barangay": "Malate"
   }
   ```

## Use Cases & Analytics

### Enabled Analytics
1. **Geographic Distribution**: Map campaigns by region/province/city
2. **Beneficiary Analysis**: Track total beneficiaries reached
3. **Location-based Filtering**: Filter campaigns by location
4. **Impact Reporting**: Generate regional impact reports
5. **Donor Matching**: Match donors with campaigns in their region

### Future Enhancements
1. **Map Visualization**: Display campaigns on interactive map
2. **Location Filters**: Add region/province filters on public pages
3. **Nearby Campaigns**: Show campaigns near donor's location
4. **Regional Statistics**: Dashboard showing campaigns per region
5. **Beneficiary Count Tracking**: Parse beneficiary text for numbers

## API Examples

### Create Campaign with Beneficiary & Location
```json
POST /api/charities/{id}/campaigns
{
  "title": "School Supplies for Tacloban Students",
  "description": "Help rebuild education after disaster",
  "campaign_type": "education",
  "beneficiary": "800 elementary students in Tacloban City who lost school supplies in the typhoon",
  "region": "Eastern Visayas",
  "province": "Leyte",
  "city": "Tacloban City",
  "barangay": "San Jose",
  "target_amount": 100000,
  "donation_type": "one_time",
  "problem": "Students lack basic supplies...",
  "solution": "Provide comprehensive kits...",
  "status": "published"
}
```

### Update Campaign Location
```json
PUT /api/campaigns/{id}
{
  "beneficiary": "1000 students (updated count)",
  "region": "Central Visayas",
  "province": "Cebu",
  "city": "Cebu City"
}
```

## Files Modified

### Backend (3 files)
1. `database/migrations/2025_01_24_000001_add_beneficiary_and_location_to_campaigns_table.php` (new)
2. `app/Models/Campaign.php`
3. `app/Http/Controllers/CampaignController.php`

### Frontend (1 file)
1. `components/charity/CreateCampaignModal.tsx`

### Reused Components
1. `components/forms/PhilippineAddressForm.tsx` (existing)
2. `hooks/usePhilippineLocations.ts` (existing)

## Component Layout in Modal

```
Create Campaign Modal
├── Campaign Title
├── About This Campaign
├── Story Fields
│   ├── The Problem
│   ├── The Solution
│   └── Expected Outcome
├── [NEW] Beneficiary & Location
│   ├── Beneficiaries (textarea)
│   └── Campaign Location
│       ├── Region (dropdown)
│       ├── Province (dropdown)
│       ├── City (dropdown)
│       └── Barangay (text)
├── Financials & Media
├── Type of Campaign
├── Settings
└── Donation Channels
```

## Data Structure

```typescript
interface Campaign {
  id: number;
  title: string;
  description: string;
  problem: string;
  solution: string;
  expected_outcome?: string;
  beneficiary?: string;        // NEW
  region?: string;             // NEW
  province?: string;           // NEW
  city?: string;               // NEW
  barangay?: string;           // NEW
  target_amount: number;
  campaign_type: string;
  donation_type: string;
  status: string;
  // ... other fields
}
```

All fields are optional and will be `null` if not provided during campaign creation.
