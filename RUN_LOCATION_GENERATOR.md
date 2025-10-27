# 🚀 Generate Complete Philippine Location Data

## Quick Instructions

### Step 1: Run the Generator Script

```bash
cd c:\Users\sagan\Capstone
node scripts/generateLocationData.js
```

**What it does:**
- Fetches all regions, provinces, cities, and barangays from PSGC API
- Generates complete `philippineLocations.ts` file
- Shows progress with detailed logging
- Creates file with 40,000+ barangays

**Time:** ~10-30 minutes (depending on API speed)

---

## Alternative: Use Pre-Built Package

If the script is too slow, install this package:

```bash
cd capstone_frontend
npm install philippine-location-json-for-geer
```

Then update your `philippineLocations.ts`:

```typescript
import locations from 'philippine-location-json-for-geer';

export const philippineLocations = locations.map(region => ({
  name: region.region_name,
  provinces: region.province_list.map(province => ({
    name: province.province_name,
    cities: province.municipality_list.map(city => ({
      name: city.municipality_name,
      barangays: city.barangay_list.map(b => b.brgy_name)
    }))
  }))
}));

// ... rest of helper functions
```

---

## What You'll Get

### Complete Data:
- ✅ 17 Regions
- ✅ 81 Provinces  
- ✅ 1,488 Cities/Municipalities
- ✅ 42,000+ Barangays

### File Size:
- Approximately 10-15 MB
- Minified and tree-shakeable
- All data in one file

---

## Troubleshooting

### Error: "fetch is not defined"
**Solution:** Install node-fetch
```bash
npm install node-fetch
```

Then update script line 1:
```javascript
const fetch = require('node-fetch');
```

### Error: "Cannot find module 'fs'"
**Solution:** You're using browser environment. Run with Node.js:
```bash
node scripts/generateLocationData.js
```

### API Too Slow / Rate Limited
**Solution:** Use the npm package instead (see Alternative above)

---

## After Generation

### Verify the file:
```bash
# Check file exists
ls capstone_frontend/src/data/philippineLocations.ts

# Check file size
du -h capstone_frontend/src/data/philippineLocations.ts
```

### Test in your app:
```typescript
import { getRegions, getStats } from '@/data/philippineLocations';

console.log('Regions:', getRegions());
console.log('Stats:', getStats());
```

---

## Expected Output

```
🚀 Starting PSGC data fetch...

📍 Fetching regions...
✅ Found 17 regions

📍 Processing NCR - National Capital Region...
  ✅ Found 1 provinces
    📍 Processing Metro Manila...
      ✅ Found 16 cities/municipalities
        📍 Processing Manila...
          ✅ Found 896 barangays
        📍 Processing Quezon City...
          ✅ Found 142 barangays
        ...
✅ Completed NCR - National Capital Region

...

✅ SUCCESS! File generated at:
   c:\Users\sagan\Capstone\capstone_frontend\src\data\philippineLocations.ts

📊 Data Statistics:
   Regions: 17
   Provinces: 81
   Cities/Municipalities: 1,488
   Barangays: 42,046

📦 File Size: 12.34 MB

✨ Done! You can now use the updated philippineLocations.ts file.
```

---

## Next Steps

1. **Run the script** (or install npm package)
2. **Wait for completion** (~10-30 minutes)
3. **Restart your dev server**
4. **Test the LocationSelector** - should now have ALL locations!

---

**Ready to run? Just execute:**
```bash
node scripts/generateLocationData.js
```
