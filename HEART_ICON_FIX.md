# Heart Icon Fix - Stays Red After Refresh ✅

## Problem
When you like a post, the heart turns red. But when you refresh the page, the heart becomes gray again even though you already liked it.

## Root Cause
The frontend was **not preserving** the `is_liked` field from the backend when mapping the updates data.

## What Was Wrong
```tsx
// BEFORE - is_liked was being lost
const updatesWithCharity = charityUpdates.map((u: any) => ({
  ...u,
  children: u.children || [],  // ❌ Lost is_liked for children
  charity: { ... },
  // ❌ is_liked from backend was being overwritten
}));
```

## The Fix
```tsx
// AFTER - is_liked is preserved
const updatesWithCharity = charityUpdates.map((u: any) => ({
  ...u,
  children: u.children ? u.children.map((child: any) => ({
    ...child,
    is_liked: child.is_liked || false, // ✅ Preserve from backend
  })) : [],
  is_liked: u.is_liked || false, // ✅ Preserve from backend
  charity: { ... },
}));
```

## How It Works Now

1. **You like a post** → Frontend calls `/api/updates/{id}/like`
2. **Backend saves the like** → Creates record in `update_likes` table
3. **You refresh the page** → Frontend fetches updates
4. **Backend checks if you liked it** → `isLikedBy($user->id)`
5. **Backend returns `is_liked: true`** → Included in response
6. **Frontend preserves `is_liked`** → Heart stays red! ❤️

## Result

✅ Like a post → Heart turns red  
✅ Refresh the page → Heart **stays red**  
✅ Unlike the post → Heart turns gray  
✅ Refresh again → Heart **stays gray**  

The heart icon now correctly reflects your like status even after refreshing! 🎉

---

**File Changed:** `capstone_frontend/src/pages/donor/CommunityFeed.tsx`  
**Lines Changed:** 3 lines (preserving is_liked for parent and children)  
**Status:** ✅ FIXED
