# Clickable Charity Logos Implementation

## Overview
All charity logos on the Updates page are now clickable and navigate to the charity's public profile page.

## Locations Updated

### 1. Left Sidebar - Charity Logo
**Location**: Top of left panel

**Features**:
- Large charity logo (20x20)
- Cursor changes to pointer on hover
- Ring effect intensifies on hover
- Clicks navigate to `/charity-profile/{charityId}`

**Visual Feedback**:
- `cursor-pointer` - Shows hand cursor
- `hover:ring-primary/30` - Ring glows on hover
- `transition-all` - Smooth animation

### 2. Post Headers - Charity Logo
**Location**: Each post in the feed

**Features**:
- Medium charity logo (11x11)
- Cursor changes to pointer on hover
- Ring effect on hover
- Clicks navigate to `/charity-profile/{charityId}`

**Visual Feedback**:
- `cursor-pointer` - Shows hand cursor
- `hover:ring-primary/50` - Ring glows on hover
- `transition-all` - Smooth animation

### 3. Comments - Charity Logos (Feed View)
**Location**: Comment avatars in expanded comments section

**Features**:
- Small charity logo (8x8 or 9x9)
- Only clickable for charity admin comments
- Cursor changes to pointer on hover
- Ring appears on hover
- Clicks navigate to `/charity-profile/{charityId}`

**Logic**:
```typescript
onClick={() => {
  if (comment.user?.role === "charity_admin" && comment.user?.charity?.id) {
    window.location.href = `/charity-profile/${comment.user.charity.id}`;
  }
}}
```

**Visual Feedback**:
- `cursor-pointer` - Shows hand cursor
- `hover:ring-2 hover:ring-primary/50` - Ring appears on hover
- `transition-all` - Smooth animation

### 4. Comments - Charity Logos (Modal View)
**Location**: Comment avatars in post detail modal

**Features**:
- Small charity logo (8x8)
- Only clickable for charity admin comments
- Same behavior as feed view comments
- Clicks navigate to `/charity-profile/{charityId}`

**Visual Feedback**:
- `cursor-pointer` - Shows hand cursor
- `hover:ring-primary/50` - Ring glows on hover
- `transition-all` - Smooth animation

## User Experience

### Hover States
All clickable logos show visual feedback:
1. **Cursor Change**: Pointer (hand) cursor appears
2. **Ring Effect**: Colored ring appears or intensifies
3. **Smooth Transition**: All effects animate smoothly

### Click Behavior
- Navigates to charity's public profile page
- URL format: `/charity-profile/{charityId}`
- Works for:
  - Own charity logo (left sidebar)
  - Own charity logo (post headers)
  - Other charity logos (in comments, if charity admin)

### Non-Clickable Avatars
- Donor profile images in comments remain non-clickable
- Only charity admin avatars are clickable

## Implementation Details

### CSS Classes Added
```typescript
className="cursor-pointer hover:ring-primary/30 transition-all"  // Left sidebar
className="cursor-pointer hover:ring-primary/50 transition-all"  // Post headers
className="cursor-pointer hover:ring-2 hover:ring-primary/50 transition-all"  // Comments
```

### Navigation Method
```typescript
window.location.href = `/charity-profile/${charityId}`
```

### Conditional Rendering
Comments only clickable for charity admins:
```typescript
if (comment.user?.role === "charity_admin" && comment.user?.charity?.id) {
  // Navigate to profile
}
```

## Benefits

1. **Better Navigation**: Easy access to charity profiles
2. **Intuitive UX**: Users expect logos to be clickable
3. **Visual Feedback**: Clear hover states indicate interactivity
4. **Consistent**: All charity logos behave the same way
5. **Professional**: Matches social media platform standards

## Testing Checklist

- [x] Left sidebar logo clickable
- [x] Post header logo clickable
- [x] Comment charity logos clickable (feed view)
- [x] Comment charity logos clickable (modal view)
- [x] Hover effects work on all logos
- [x] Navigation goes to correct charity profile
- [x] Donor avatars remain non-clickable
- [ ] Test with multiple charities commenting
- [ ] Test navigation in different browsers
