# Final Comment Like Button Fix ✅

## What Was Changed

Updated **both** comment sections to show like count instead of "Like" text:

### 1. CommentSection.tsx (Below posts)
✅ Added Heart icon import
✅ Added likes_count and is_liked to interface
✅ Changed Like button to show count

### 2. PostCard.tsx (Modal comments)
✅ Added likes_count and is_liked to interface
✅ Changed Like button from "Like" text to count

## Before vs After

### Before (Wrong):
```
7h ago  ❤️ Like  ↩️ Reply
```

### After (Correct - Matches Charity Updates):
```
7h ago  ❤️ 1  ↩️ Reply
```

## Files Modified

### 1. `src/components/newsfeed/CommentSection.tsx`
**Lines changed:**
- Line 8: Added `Heart` import
- Lines 25-26: Added `likes_count?: number; is_liked?: boolean;`
- Lines 247-262: Updated Like button to show count

**Like Button Code:**
```typescript
<Button
  className={`h-auto p-0 text-xs font-semibold ${
    comment.is_liked
      ? "text-red-500 hover:text-red-600"
      : "text-muted-foreground hover:text-red-500"
  }`}
>
  <Heart className={`h-3 w-3 mr-1 ${comment.is_liked ? "fill-current" : ""}`} />
  {comment.likes_count && comment.likes_count > 0 ? comment.likes_count : ''}
</Button>
```

### 2. `src/components/newsfeed/PostCard.tsx`
**Lines changed:**
- Lines 72-73: Added `likes_count?: number; is_liked?: boolean;`
- Lines 562-577: Updated Like button to show count

**Like Button Code:**
```typescript
<Button
  className={`h-auto p-0 text-xs font-semibold transition-colors ${
    comment.is_liked
      ? "text-red-500 hover:text-red-600"
      : "text-muted-foreground hover:text-red-500"
  }`}
>
  <Heart
    className={`h-3 w-3 mr-1 ${comment.is_liked ? "fill-current" : ""}`}
  />
  {comment.likes_count && comment.likes_count > 0 ? comment.likes_count : ''}
</Button>
```

## How It Works

### No Likes (0):
- Shows: `❤️` (just the heart icon, no number)

### Has Likes (1+):
- Shows: `❤️ 1` or `❤️ 5` (heart + number)

### When Liked:
- Heart is **filled** and **red**
- Text is **red**

### When Not Liked:
- Heart is **outline** and **gray**
- Text is **gray**

## Where This Applies

### 1. Donor Newsfeed
- Comments below posts
- ✅ Now shows: `Time · ❤️ Count · Reply`

### 2. Charity Profile Updates Tab
- Comments in updates
- ✅ Now shows: `Time · ❤️ Count · Reply`

### 3. Full-Screen Modal
- Comments in image modal
- ✅ Now shows: `Time · ❤️ Count · Reply`

## Testing

### Test in Donor Newsfeed:
1. Go to `/donor/feed`
2. Click on a post's comments
3. ✅ Should see: `7h ago ❤️ 1 Reply`

### Test in Charity Profile:
1. Go to `/donor/charities/:id`
2. Click Updates tab
3. View comments
4. ✅ Should see: `7h ago ❤️ 1 Reply`

### Test in Modal:
1. Click an image in a post
2. View comments in modal
3. ✅ Should see: `7h ago ❤️ 1 Reply`

## Summary

✅ **CommentSection.tsx updated** - Shows count  
✅ **PostCard.tsx modal updated** - Shows count  
✅ **Both interfaces updated** - Added like fields  
✅ **Matches charity updates exactly** - Same format  
✅ **Shows count when > 0** - Hides when 0  
✅ **Red when liked** - Gray when not  
✅ **Filled when liked** - Outline when not  

All comment sections now match the charity updates page! 🎉
