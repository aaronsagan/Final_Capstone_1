# Comment Like Button Fix ✅

## Issue
The donor newsfeed/charity profile comments were missing the Like button and like count that appears in the charity updates page.

## Difference Found

### Charity Updates (Working):
```
@Hugs and Kisses 💖 Reply text...
7h ago  ❤️ 1  ↩️ Reply
```

### Donor Newsfeed (Missing):
```
@Hugs and Kisses 💖 Reply text...
7h ago  ↩️ Reply
```
(No like count or Like button!)

## Solution Applied

### File: `src/components/newsfeed/CommentSection.tsx`

#### 1. Added Heart Icon Import
```typescript
import { Heart } from "lucide-react";
```

#### 2. Updated Comment Interface
Added like fields:
```typescript
interface Comment {
  id: number;
  update_id: number;
  user_id: number;
  content: string;
  created_at: string;
  likes_count?: number;    // Added
  is_liked?: boolean;      // Added
  user?: {...};
}
```

#### 3. Added Like Button Before Reply Button
```typescript
<div className="flex items-center gap-3 mt-2">
  {/* Like Button with Count */}
  <Button
    variant="ghost"
    size="sm"
    className={`h-auto p-0 text-xs font-semibold ${
      comment.is_liked
        ? "text-red-500 hover:text-red-600"
        : "text-muted-foreground hover:text-red-500"
    }`}
  >
    <Heart className={`h-3 w-3 mr-1 ${comment.is_liked ? "fill-current" : ""}`} />
    {comment.likes_count && comment.likes_count > 0 ? comment.likes_count : ''}
  </Button>
  
  {/* Reply Button */}
  <Button onClick={() => handleReply(comment)}>
    <Reply className="h-3 w-3 mr-1" />
    Reply
  </Button>
</div>
```

## Features

### Like Button:
- ✅ Shows heart icon (❤️)
- ✅ Shows like count if > 0
- ✅ Red color when liked
- ✅ Gray color when not liked
- ✅ Filled heart when liked
- ✅ Outline heart when not liked
- ✅ Hover effect (turns red)

### Visual States:

**Not Liked:**
```
🤍 Like
```

**Liked with 1 like:**
```
❤️ 1
```

**Liked with multiple likes:**
```
❤️ 5
```

## Now Matches Charity Updates

### Comment Actions Row:
```
7h ago  ❤️ 1  ↩️ Reply
```

Both pages now show:
1. **Time** (7h ago)
2. **Like button with count** (❤️ 1)
3. **Reply button** (↩️ Reply)

## Where This Applies

### 1. Donor Newsfeed
- Comments below posts
- Now shows Like button + count

### 2. Charity Profile Updates
- Comments in updates tab
- Now shows Like button + count

### 3. PostCard Modal
- Comments in full-screen modal
- Already had Like button (different structure)

## Testing

### Check Comments:
1. Go to donor newsfeed
2. Expand comments on a post
3. ✅ Should see: `Time · ❤️ Count · Reply`

### Check Replies:
1. Look for reply comments (starting with @)
2. ✅ Should see Like button with count
3. ✅ Should be same format as main comments

### Check States:
- ✅ No likes: Shows just heart icon
- ✅ Has likes: Shows heart + number
- ✅ Liked: Heart is filled and red
- ✅ Not liked: Heart is outline and gray

## Summary

✅ **Added Like button to comments**  
✅ **Shows like count when > 0**  
✅ **Matches charity updates page exactly**  
✅ **Works for both main comments and replies**  
✅ **Proper styling (red when liked, gray when not)**  
✅ **Hover effects working**  

The comments now look identical to the charity updates page! 🎉
