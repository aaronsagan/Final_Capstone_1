# Charity Registration Modal Scroll Fix ✓

## Problem

When viewing charity applications with many documents, the modal stretched down the entire screen, making it difficult to use and causing the page behind to scroll.

## Solution Applied

Modified the charity details modal to have:
1. **Maximum height**: 85% of viewport height (`max-h-[85vh]`)
2. **Scrollable content**: ScrollArea component for internal scrolling
3. **Fixed layout**: Flexbox layout to keep header and footer fixed
4. **Background stays fixed**: Page behind modal doesn't scroll

## Changes Made

**File**: `capstone_frontend/src/pages/admin/Charities.tsx`

### 1. Added ScrollArea Import
```tsx
import { ScrollArea } from "@/components/ui/scroll-area";
```

### 2. Updated Dialog Structure

**Before:**
```tsx
<DialogContent className="max-w-2xl">
  <DialogHeader>...</DialogHeader>
  <div className="grid gap-4 py-4">
    {/* All content */}
  </div>
  <DialogFooter>...</DialogFooter>
</DialogContent>
```

**After:**
```tsx
<DialogContent className="max-w-2xl max-h-[85vh] flex flex-col">
  <DialogHeader>...</DialogHeader>
  <ScrollArea className="flex-1 pr-4">
    <div className="grid gap-4 py-4">
      {/* All content - now scrollable */}
    </div>
  </ScrollArea>
  <DialogFooter>...</DialogFooter>
</DialogContent>
```

## Key CSS Classes Applied

| Class | Purpose |
|-------|---------|
| `max-h-[85vh]` | Limits modal height to 85% of viewport |
| `flex flex-col` | Vertical flexbox layout |
| `flex-1` | ScrollArea takes remaining space |
| `pr-4` | Right padding for scrollbar space |

## How It Works

### Layout Structure:
```
┌─────────────────────────────────┐
│ DialogHeader (Fixed)            │
├─────────────────────────────────┤
│ ┌─────────────────────────────┐ │
│ │ ScrollArea (Scrollable)     │ │
│ │                             │ │
│ │ • Organization Name         │ │
│ │ • Email                     │ │
│ │ • Registration Number       │ │
│ │ • Mission                   │ │
│ │ • Status                    │ │
│ │ • Documents (many items)    │ │
│ │   - Document 1              │ │
│ │   - Document 2              │ │
│ │   - Document 3              │ │
│ │   - ...                     │ │
│ │                             │ │
│ └─────────────────────────────┘ │
├─────────────────────────────────┤
│ DialogFooter (Fixed)            │
│ [Request Info] [Reject] [Approve]│
└─────────────────────────────────┘
```

## Benefits

### Before:
- ❌ Modal stretched entire screen height
- ❌ Difficult to see action buttons
- ❌ Background page scrolled
- ❌ Poor user experience with many documents

### After:
- ✅ Modal has consistent, manageable size
- ✅ Header and footer always visible
- ✅ Smooth scrolling within modal
- ✅ Background page stays fixed
- ✅ Professional, polished appearance
- ✅ Easy to review all documents

## Testing

1. **Go to Admin Dashboard** → Charity Registrations
2. **Click View (eye icon)** on a charity with many documents
3. **Observe**:
   - Modal appears at consistent height
   - Header shows "Charity Application Details"
   - Content area is scrollable
   - Footer buttons (Request Info, Reject, Approve) always visible
   - Background page doesn't scroll
4. **Scroll within modal** to see all documents
5. **Close modal** - background page position unchanged

## Responsive Behavior

- **Desktop**: Modal uses 85% of viewport height
- **Tablet**: Same behavior, adapts to screen size
- **Mobile**: Modal adjusts to smaller screens while maintaining scroll

## Additional Notes

- The `pr-4` padding ensures the scrollbar doesn't overlap content
- The `flex-1` class makes ScrollArea take all available space between header and footer
- The modal backdrop prevents interaction with background content
- ESC key or clicking outside still closes the modal

Modal is now user-friendly and professional! 🎉
