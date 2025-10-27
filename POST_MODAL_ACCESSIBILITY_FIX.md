# Post Modal Accessibility & Size Fixes

## Issues Fixed

### 1. Dialog Accessibility Warnings
**Problem:** Console errors about missing `DialogTitle` and `aria-describedby` for DialogContent components.

**Solution:** 
- Added `VisuallyHidden` component to `dialog.tsx` for screen reader accessibility
- Wrapped DialogTitle and DialogDescription in VisuallyHidden for the post modal
- This maintains accessibility without showing visual titles

### 2. Post Image Sizes (Facebook Standard)
**Problem:** Post images were too large and not following Facebook's standard sizing.

**Solution:** Adjusted image heights to Facebook-like standards:
- **Single image:** `max-h-[450px]` (was 500px)
- **Two images:** `h-[280px]` (was 300px)
- **Three images:**
  - Main image: `min-h-[350px] max-h-[450px]` (was 400px)
  - Side images: `h-[172px]` (was 199px)
- **Four+ images:** `h-[180px]` (was 200px)

### 3. Comments Section Layout
**Problem:** Comments were stretched and not visible properly in the modal.

**Solution:**
- Added `max-h-[98vh]` to the right panel container
- Changed main image from `h-full w-full object-contain p-4` to `max-h-[90vh] max-w-full object-contain`
- This ensures the comments section stays within viewport and is scrollable

## Files Modified

### 1. `capstone_frontend/src/components/ui/dialog.tsx`
- Added `VisuallyHidden` component for accessibility
- Exported VisuallyHidden for use in other components

### 2. `capstone_frontend/src/pages/charity/CharityUpdates.tsx`
- Imported `DialogDescription` and `VisuallyHidden`
- Added hidden DialogTitle and DialogDescription to post modal
- Adjusted all post image max-heights to Facebook standards
- Fixed modal layout to prevent comment section stretching

## Testing

To verify the fixes:
1. Open the Charity Updates page
2. Click on any post image to open the modal
3. Check browser console - no more DialogContent warnings
4. Verify images are properly sized (not too tall)
5. Verify comments section is visible at the bottom
6. Test scrolling in the comments area

## Accessibility Improvements

The VisuallyHidden component:
- Makes content accessible to screen readers
- Hides content visually from sighted users
- Uses CSS classes: `absolute w-px h-px p-0 -m-px overflow-hidden whitespace-nowrap border-0`
- Follows WCAG 2.1 accessibility guidelines

## Facebook-Style Sizing Reference

The new sizing matches Facebook's post image standards:
- Single images: ~450px max height
- Multiple images: Grid layout with smaller, uniform heights
- Maintains aspect ratios with `object-cover`
- Responsive and mobile-friendly
