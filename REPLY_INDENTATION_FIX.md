# Reply Indentation Fix ✅

## Issue
Replies starting with `@Username` were NOT indented in the CommentSection component, making them look the same as main comments.

## What Was Missing
The CommentSection component didn't have the reply detection and indentation logic that the charity updates page has.

## Solution Applied

### File: `src/components/newsfeed/CommentSection.tsx`

#### Added Reply Detection:
```typescript
comments.map((comment) => {
  const isReply = comment.content.startsWith('@');
  return (
    // ... comment JSX
  );
})
```

#### Added Conditional Indentation:
```typescript
<div className={`group flex gap-3 p-3 rounded-lg bg-muted/30 ${isReply ? 'ml-12' : ''}`}>
```

#### Added Smaller Avatar for Replies:
```typescript
<Avatar className={`${isReply ? 'w-7 h-7' : 'w-8 h-8'} shrink-0`}>
```

## Before vs After

### Before (Wrong):
```
👤 Hugs and Kisses
   Main comment text...
   
👤 Hugs and Kisses
   @Hugs and Kisses Reply text...  ← NOT indented!
```

### After (Correct):
```
👤 Hugs and Kisses
   Main comment text...
   
   👤 Hugs and Kisses
      @Hugs and Kisses Reply text...  ← Indented!
```

## Changes Made

### 1. Wrapped map in function with reply detection:
```typescript
comments.map((comment) => {
  const isReply = comment.content.startsWith('@');
  return (
    <div className={`... ${isReply ? 'ml-12' : ''}`}>
      <Avatar className={`${isReply ? 'w-7 h-7' : 'w-8 h-8'} ...`}>
      {/* rest of comment */}
    </div>
  );
})
```

### 2. Applied indentation:
- **Main comments:** No indentation (`ml-0`)
- **Replies:** Indented 48px (`ml-12`)

### 3. Applied size difference:
- **Main comments:** 32px avatar (`w-8 h-8`)
- **Replies:** 28px avatar (`w-7 h-7`)

## Now Matches Charity Updates

Both pages now show:
- Main comments: Full size, no indent
- Replies: Smaller, indented to the right

## Where This Applies

### 1. Donor Newsfeed
- Comments below posts
- ✅ Replies now indented

### 2. Charity Profile Updates
- Comments in updates tab
- ✅ Replies now indented

### 3. PostCard Modal
- Already had indentation
- ✅ Still working

## Testing

### Check Indentation:
1. Go to donor newsfeed
2. Find a post with reply comments (starting with @)
3. ✅ Replies should be indented to the right
4. ✅ Replies should have smaller avatars

### Visual Check:
```
Main Comment (no indent):
👤 User Name
   Comment text

   Reply Comment (indented):
   👤 User Name
      @User Reply text
```

## Summary

✅ **Added reply detection** - Checks if content starts with @  
✅ **Added indentation** - Replies indented 48px (ml-12)  
✅ **Added size difference** - Replies have smaller avatars  
✅ **Fixed syntax** - Properly closed map function  
✅ **Matches charity updates** - Same visual hierarchy  

Replies are now properly nested and indented! 🎉
