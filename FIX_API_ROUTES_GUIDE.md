# API Routes Fix Guide

## Critical Issue Found! 🚨

Many files are incorrectly constructing storage URLs using:
```typescript
`${API_URL}/storage/${path}`
```

This creates **WRONG** URLs like:
- `http://127.0.0.1:8000/api/storage/image.jpg` ❌

Should be:
- `http://127.0.0.1:8000/storage/image.jpg` ✅

## The Problem

`API_URL` = `http://127.0.0.1:8000/api`

When you do `${API_URL}/storage/`, you get:
`http://127.0.0.1:8000/api/storage/` ❌ WRONG!

Laravel serves storage from the root, not through the API route.

## The Solution

Use the centralized `buildStorageUrl()` function from `@/lib/api`:

### Before (WRONG):
```typescript
const API_URL = import.meta.env.VITE_API_URL;
<img src={`${API_URL}/storage/${charity.logo_path}`} />
```

### After (CORRECT):
```typescript
import { buildStorageUrl } from '@/lib/api';
<img src={buildStorageUrl(charity.logo_path) || ''} />
```

## Files That Need Fixing

### High Priority (Storage URL Issues)
1. ✅ `src/pages/charity/Bin.tsx` - FIXED
2. `src/pages/donor/CommunityNewsfeed.tsx` - 2 storage URLs
3. `src/pages/donor/CharityProfile.tsx` - 7 storage URLs
4. `src/pages/CharityPublicProfile.tsx` - 4 storage URLs
5. `src/pages/donor/DonorDashboardHome.tsx` - 4 storage URLs
6. `src/pages/CharityDetail.tsx` - 3 storage URLs
7. `src/pages/donor/Profile.tsx` - 3 storage URLs

### Components
- `src/components/newsfeed/ThreadSection.tsx` - 2 storage URLs
- `src/components/charity/CampaignCard.tsx` - 1 storage URL
- `src/components/charity/DonationsModal.tsx` - 1 storage URL
- `src/components/donor/CharityCard.tsx` - 1 storage URL

### Other Pages
- Various charity, donor, and admin pages with 1-2 storage URLs each

## Quick Fix Pattern

### For Image Tags:
```typescript
// BEFORE
<img src={`${API_URL}/storage/${path}`} />

// AFTER
import { buildStorageUrl } from '@/lib/api';
<img src={buildStorageUrl(path) || ''} />
```

### For Avatar Images:
```typescript
// BEFORE
<AvatarImage src={logoPath ? `${API_URL}/storage/${logoPath}` : undefined} />

// AFTER
import { buildStorageUrl } from '@/lib/api';
<AvatarImage src={buildStorageUrl(logoPath) || undefined} />
```

### For Background Images:
```typescript
// BEFORE
style={{ backgroundImage: `url(${API_URL}/storage/${path})` }}

// AFTER
import { buildStorageUrl } from '@/lib/api';
style={{ backgroundImage: `url(${buildStorageUrl(path)})` }}
```

## Automated Fix Steps

1. Add import: `import { buildStorageUrl } from '@/lib/api';`
2. Replace all instances of: `${API_URL}/storage/${variable}`
3. With: `buildStorageUrl(variable) || ''`
4. Remove local `API_URL` constant if only used for storage

## Testing After Fix

1. Check browser console for 404 errors on images
2. Verify images load correctly
3. Check Network tab - URLs should be `http://127.0.0.1:8000/storage/...`
4. NOT `http://127.0.0.1:8000/api/storage/...`

## Why This Matters

- **Current (WRONG):** Images return 404 errors
- **After Fix (CORRECT):** Images load properly
- **Production Impact:** Critical for deployed applications

## Status

- ✅ Centralized API utility created (`lib/api.ts`)
- ✅ Storage helper updated (`lib/storage.ts`)
- ✅ Critical hardcoded URLs fixed (Bin.tsx, usePhilippineLocations.ts)
- ⚠️ **NEXT:** Fix storage URL construction in remaining files

Would you like me to automatically fix all these files?
