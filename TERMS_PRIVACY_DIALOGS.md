# Terms & Privacy as Pop-up Dialogs ✓

## What Changed

Instead of opening new tabs, the Terms of Service and Privacy Policy now appear as **modal dialogs** (pop-ups) when clicked during registration.

## Files Created

### Dialog Components:
1. **`src/components/legal/DonorTermsDialog.tsx`** - Donor Terms modal
2. **`src/components/legal/DonorPrivacyDialog.tsx`** - Donor Privacy modal
3. **`src/components/legal/CharityTermsDialog.tsx`** - Charity Terms modal
4. **`src/components/legal/CharityPrivacyDialog.tsx`** - Charity Privacy modal

## Features

### Modal Dialog Design:
- ✅ **Large modal**: `max-w-4xl` for comfortable reading
- ✅ **Scrollable content**: `ScrollArea` with 60vh height
- ✅ **Professional header**: Title and effective date
- ✅ **Easy to close**: Click outside or press ESC
- ✅ **Responsive**: Works on all screen sizes
- ✅ **Same content**: All your legal text included
- ✅ **Bold section titles**: No numbers, just bold headings

### User Experience:
- Click "Terms of Service" → Modal pops up
- Click "Privacy Policy" → Modal pops up
- Read the content without leaving the registration page
- Close modal and continue registration
- No new tabs cluttering the browser

## Updated Registration Pages

### Donor Registration (`RegisterDonor.tsx`):
**Before:**
```tsx
<Link to="/legal/donor/terms" target="_blank">
  Terms of Service
</Link>
```

**After:**
```tsx
<button
  type="button"
  onClick={() => setShowTermsDialog(true)}
  className="text-primary hover:underline"
>
  Terms of Service
</button>
```

### Charity Registration (`RegisterCharity.tsx`):
Same pattern - buttons instead of links that trigger dialogs

## How It Works

1. **User clicks "Terms of Service"**
   - `setShowTermsDialog(true)` is called
   - Dialog component receives `open={true}`
   - Modal appears with full terms content

2. **User reads the content**
   - Can scroll through all sections
   - Content is identical to the full pages

3. **User closes dialog**
   - Clicks outside, presses ESC, or clicks X
   - `onOpenChange(false)` is called
   - Returns to registration form

4. **User continues registration**
   - No page reload
   - No lost form data
   - Smooth experience

## Dialog Structure

Each dialog includes:
```tsx
<Dialog open={open} onOpenChange={onOpenChange}>
  <DialogContent className="max-w-4xl max-h-[80vh]">
    <DialogHeader>
      <DialogTitle>Terms of Service for Donors</DialogTitle>
      <p>Effective Date: October 13, 2025</p>
    </DialogHeader>
    <ScrollArea className="h-[60vh]">
      {/* All content with bold section titles */}
    </ScrollArea>
  </DialogContent>
</Dialog>
```

## Benefits

### For Users:
- ✅ **No context switching**: Stay on registration page
- ✅ **No lost data**: Form data preserved
- ✅ **Quick access**: Instant pop-up
- ✅ **Easy to close**: Multiple ways to dismiss
- ✅ **Better UX**: Smooth, modern experience

### For Developers:
- ✅ **Reusable components**: Clean dialog components
- ✅ **State management**: Simple boolean state
- ✅ **Maintainable**: Content in one place
- ✅ **Consistent**: Same pattern for all dialogs

## Testing

### Donor Registration:
1. Go to `http://localhost:8081/auth/register/donor`
2. Scroll to "Terms" checkbox
3. Click "Terms of Service" → Dialog pops up
4. Click "Privacy Policy" → Dialog pops up
5. Close dialogs and continue registration

### Charity Registration:
1. Go to `http://localhost:8081/auth/register/charity`
2. Complete steps 1-3
3. On Step 4 (Review & Submit), find the checkboxes
4. Click "Terms of Service" → Dialog pops up
5. Click "Privacy Policy" → Dialog pops up
6. Close dialogs and continue

## Technical Details

### State Management:
```tsx
const [showTermsDialog, setShowTermsDialog] = useState(false);
const [showPrivacyDialog, setShowPrivacyDialog] = useState(false);
```

### Dialog Rendering:
```tsx
<DonorTermsDialog 
  open={showTermsDialog} 
  onOpenChange={setShowTermsDialog} 
/>
```

### Button Triggers:
```tsx
<button
  type="button"
  onClick={() => setShowTermsDialog(true)}
  className="text-primary hover:underline"
>
  Terms of Service
</button>
```

## Styling

- **Dialog**: Large, centered, with backdrop
- **Content**: Scrollable with proper spacing
- **Typography**: Same as full pages (bold titles, proper spacing)
- **Colors**: Matches app theme (light/dark mode support)
- **Responsive**: Adapts to mobile screens

All legal content now appears as convenient pop-ups instead of new tabs! 🎉
