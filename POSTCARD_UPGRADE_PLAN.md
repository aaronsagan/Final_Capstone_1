# PostCard Component Upgrade Plan

## Goal
Upgrade PostCard component to match the charity dashboard updates page with:
1. Advanced image grid layout (1-4+ images with Facebook-style arrangement)
2. Full-screen image modal with comments sidebar
3. Image navigation (prev/next)
4. Better comment/reply structure

## Current PostCard Issues

### Image Grid (Lines 227-244)
**Current:** Simple 1 or 2 column grid
```typescript
className={cn(
  "grid gap-2 rounded-lg overflow-hidden",
  update.media_urls.length === 1 ? "grid-cols-1" : "grid-cols-2"
)}
```

**Needed:** Facebook-style adaptive grid
- 1 image: Full width, max 450px
- 2 images: Side by side, 280px
- 3 images: First spans 2 rows, others stack
- 4+ images: 2x2 grid, 180px

### Modal
**Current:** No modal - images just display inline

**Needed:** Full-screen modal like charity updates
- Black background
- Image viewer on left
- Comments sidebar on right (350px)
- Image navigation arrows
- Image counter

### Comments
**Current:** Uses CommentSection component (good)

**Needed:** Keep current but add modal view

## Files to Update

### 1. PostCard.tsx
- Add image grid logic from CharityUpdates.tsx
- Add modal state and handlers
- Add image navigation
- Keep existing comment functionality

### 2. CharityProfile.tsx (Updates Tab)
- Already using PostCard ✅
- Will automatically get upgrades

### 3. CommunityNewsfeed.tsx
- Already using PostCard ✅
- Will automatically get upgrades

## Implementation Steps

1. **Add Modal State to PostCard**
   - `isModalOpen`
   - `selectedImageIndex`
   - Modal open/close handlers
   - Image navigation handlers

2. **Update Image Grid**
   - Copy grid logic from CharityUpdates.tsx (lines 624-656)
   - Add click handlers to open modal
   - Keep hover effects

3. **Add Modal Component**
   - Copy modal from CharityUpdates.tsx (lines 1527-1891)
   - Adapt for PostCard props
   - Keep comment section integration

4. **Add Required Imports**
   - ChevronLeft, ChevronRight for navigation
   - VisuallyHidden for accessibility
   - Dialog components

## Code Sections to Copy

### From CharityUpdates.tsx:

**Image Grid (Lines 624-656):**
```typescript
<div className={`grid gap-1 rounded-xl overflow-hidden ${
  update.media_urls.length === 1
    ? "grid-cols-1"
    : update.media_urls.length === 2
      ? "grid-cols-2"
      : update.media_urls.length === 3
        ? "grid-cols-2 grid-rows-2"
        : "grid-cols-2 grid-rows-2"
}`}>
  {update.media_urls.map((url, index) => (
    <img
      key={index}
      src={getStorageUrl(url) || ""}
      alt={`Update media ${index + 1}`}
      onClick={() => handleOpenModal(index)}
      className={`w-full object-cover cursor-pointer hover:opacity-90 ${
        update.media_urls.length === 1
          ? "rounded-lg max-h-[450px]"
          : update.media_urls.length === 2
            ? "rounded-lg h-[280px]"
            : update.media_urls.length === 3
              ? index === 0
                ? "rounded-lg row-span-2 h-full min-h-[350px]"
                : "rounded-lg h-[172px]"
              : "rounded-lg h-[180px]"
      }`}
    />
  ))}
</div>
```

**Modal (Lines 1527-1700):**
```typescript
<Dialog open={isModalOpen} onOpenChange={setIsModalOpen}>
  <DialogContent className="max-w-[98vw] w-full h-[98vh] p-0">
    <div className="flex h-full">
      {/* Left: Image */}
      <div className="flex-1 bg-black flex items-center justify-center">
        <Button onClick={handlePrev} className="absolute left-4">
          <ChevronLeft />
        </Button>
        <img src={currentImage} className="max-h-[90vh]" />
        <Button onClick={handleNext} className="absolute right-4">
          <ChevronRight />
        </Button>
      </div>
      
      {/* Right: Comments */}
      <div className="w-[350px] bg-card">
        {/* Header, Comments, Input */}
      </div>
    </div>
  </DialogContent>
</Dialog>
```

## Benefits

### For Donors (Newsfeed & Charity Profile):
✅ Better image viewing experience
✅ Full-screen modal for detailed viewing
✅ Easy image navigation
✅ Comments accessible while viewing images
✅ Consistent with charity dashboard

### For Development:
✅ Single component to maintain
✅ Consistent UX across all pages
✅ Reusable modal logic
✅ Better code organization

## Testing Checklist

After upgrade:
- [ ] Newsfeed images display in correct grid
- [ ] Click image opens modal
- [ ] Modal shows image + comments
- [ ] Image navigation works (prev/next)
- [ ] Comments work in modal
- [ ] Charity profile updates work same way
- [ ] Mobile responsive
- [ ] Hover effects work
- [ ] Close modal works

## Timeline

**Estimated time:** 30-45 minutes
- Copy image grid logic: 5 min
- Add modal state/handlers: 10 min
- Implement modal UI: 15 min
- Test and adjust: 10-15 min

Ready to implement?
