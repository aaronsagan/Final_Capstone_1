# Facebook-Style Comments Implementation

## Overview
Updated the comment system to match Facebook's design with better visual hierarchy and "View all comments" feature.

## Changes Made

### 1. View All Comments Feature
**Like Facebook**: Shows only the last 2 comments initially, with a button to expand.

**Implementation**:
- Shows last 2 comments by default
- "View all X comments" button appears when there are more than 2 comments
- Clicking expands to show all comments
- Uses existing `expandedComments` state to track which posts are expanded

**Code**:
```tsx
{updateComments.length > 2 && !expandedComments.has(update.id) && (
  <Button onClick={() => setExpandedComments((prev) => new Set(prev).add(update.id))}>
    View all {updateComments.length} comments
  </Button>
)}

{(expandedComments.has(update.id) ? updateComments : updateComments.slice(-2)).map((comment) => (
  // Comment display
))}
```

### 2. Improved Visual Hierarchy

**Avatar Size**:
- Increased from `h-8 w-8` to `h-9 w-9` for better visibility
- Removed ring border for cleaner look
- Added `flex-shrink-0` to prevent squishing

**Comment Bubble**:
- Updated background: `bg-muted/40` (was `bg-muted/30`)
- Adjusted padding: `px-3.5 py-2` (was `px-4 py-2.5`)
- Smoother hover effect
- More rounded appearance

**Typography**:
- Name: Increased from `text-xs` to `text-sm` for better readability
- Content: Increased from `text-sm` to `text-[15px]` (Facebook-like size)
- Action buttons: Changed to `font-semibold` for emphasis

**Spacing**:
- Reduced gap between avatar and content: `gap-2.5` (was `gap-3`)
- Tighter action button spacing: `gap-3` (was `gap-4`)
- Adjusted margins for better flow

### 3. Facebook-Style Action Buttons

**Styling**:
- Made text `font-semibold` (bolder, more prominent)
- Timestamp also `font-medium` for consistency
- Better hover states

**Layout**:
```
[Timestamp] [Like] [Reply]
   ↓         ↓       ↓
 12m ago    ❤ 5    💬 Reply
```

## Visual Comparison

### Before
```
[All comments shown]
[Scrollable area]
[Smaller text]
[More spacing]
```

### After (Facebook-style)
```
View all 10 comments  ← Button (if > 2 comments)

[Comment 1]           ← Last 2 comments shown
[Comment 2]

[Add comment box]
```

## Features

### 1. Smart Comment Display
- **≤ 2 comments**: Shows all comments
- **> 2 comments**: Shows last 2 + "View all" button
- **After clicking**: Shows all comments

### 2. Better Readability
- Larger, clearer text
- Better spacing
- More prominent action buttons
- Cleaner design

### 3. Facebook-like UX
- Most recent comments visible
- Easy to expand to see all
- Clean, uncluttered interface
- Familiar interaction pattern

## How It Works

### Initial State
```tsx
// Post with 5 comments
View all 5 comments

Comment 4 (second-to-last)
Comment 5 (most recent)

[Add comment]
```

### After Clicking "View all"
```tsx
Comment 1
Comment 2
Comment 3
Comment 4
Comment 5

[Add comment]
```

## Benefits

1. **Reduced Clutter**: Only shows recent comments initially
2. **Better Performance**: Doesn't render all comments at once
3. **Familiar UX**: Matches Facebook's pattern
4. **Improved Readability**: Larger text, better spacing
5. **Mobile-Friendly**: Less scrolling required

## Next Steps (Optional Enhancements)

- [ ] Add nested replies (indented under parent comment)
- [ ] Add "Show more replies" for nested comments
- [ ] Add comment reactions (beyond just likes)
- [ ] Add "Most relevant" vs "Newest" sorting
- [ ] Add comment timestamps on hover
- [ ] Add "Edited" indicator for edited comments

## Testing

Test these scenarios:
1. ✅ Post with 0 comments - shows add comment box
2. ✅ Post with 1-2 comments - shows all comments, no "View all" button
3. ✅ Post with 3+ comments - shows last 2 + "View all" button
4. ✅ Click "View all" - expands to show all comments
5. ✅ Add new comment - appears at bottom
6. ✅ Like/Reply buttons work correctly
