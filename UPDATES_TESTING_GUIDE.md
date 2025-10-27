# Updates Page - Testing Guide 🧪

## Quick Start

1. **Navigate to Updates Page**
   ```
   Login as charity admin → Navigate to /charity/updates
   ```

2. **Check Initial Load**
   - ✅ 3-column layout visible on desktop
   - ✅ Left panel shows charity info
   - ✅ Center shows "Updates" header
   - ✅ Right panel shows insights
   - ✅ Floating + button bottom-right

## Feature Testing Checklist

### 1. Layout & Responsiveness

#### Desktop (> 1024px)
- [ ] All 3 columns visible
- [ ] Left panel: 260px width, sticky
- [ ] Center feed: max 680px, scrollable
- [ ] Right panel: 280px width, sticky
- [ ] Floating button: bottom-right corner

#### Tablet (768px - 1024px)
- [ ] Left panel visible
- [ ] Right panel hidden
- [ ] Center feed takes remaining space
- [ ] Floating button still accessible

#### Mobile (< 768px)
- [ ] Single column layout
- [ ] Both side panels hidden
- [ ] Full-width center feed
- [ ] Floating button visible

**Test**: Resize browser window and verify layout adapts smoothly

---

### 2. Left Panel - Charity Identity

#### Visual Elements
- [ ] Charity logo displays correctly
- [ ] Avatar has ring effect
- [ ] Charity name is bold and prominent
- [ ] Mission text truncates at 60 chars
- [ ] Separator lines visible

#### Stats Section
- [ ] Updates count accurate
- [ ] Likes count accurate
- [ ] Comments count accurate
- [ ] Location shows if available
- [ ] Icons have proper colors:
  - Primary for updates
  - Red for likes
  - Blue for comments
  - Green for location

#### Action Links
- [ ] "About" button navigates to profile
- [ ] "Contact" button navigates to settings
- [ ] Hover effects work

**Test**: Create/delete posts and verify counts update

---

### 3. Center Feed - Post Cards

#### Empty State
- [ ] Shows when no updates exist
- [ ] Displays emoji and message
- [ ] "Post an Update" button works
- [ ] Opens create modal

#### Post Card Structure
- [ ] Avatar displays correctly
- [ ] Charity name is bold
- [ ] Timestamp shows (e.g., "9m ago")
- [ ] Pinned badge shows for pinned posts
- [ ] Three-dot menu accessible

#### Content Display
- [ ] Text content displays properly
- [ ] Line breaks preserved
- [ ] Long text doesn't overflow
- [ ] Font size is readable (15px)

#### Media Display
- [ ] Single image: full width
- [ ] Multiple images: 2-column grid
- [ ] Images have rounded corners
- [ ] Hover opacity effect works
- [ ] Images load correctly

#### Engagement Counts
- [ ] Like count shows if > 0
- [ ] Comment count shows if > 0
- [ ] Counts are clickable
- [ ] Clicking toggles respective action

#### Action Buttons
- [ ] Like button visible
- [ ] Comment button visible
- [ ] Both buttons have icons
- [ ] Hover effects work
- [ ] Like button fills red when liked

**Test**: Create post with 1 image, then 4 images, verify grid layout

---

### 4. Create Update Flow

#### Opening Modal
- [ ] Click floating + button
- [ ] Modal opens centered
- [ ] Backdrop overlay visible
- [ ] Modal has proper width (650px)

#### Modal Content
- [ ] Header shows "Create Update"
- [ ] Charity avatar displays
- [ ] Charity name shows
- [ ] Textarea is focused automatically
- [ ] Placeholder text visible

#### Adding Content
- [ ] Can type in textarea
- [ ] Text wraps properly
- [ ] Textarea expands if needed
- [ ] Character limit (if any) works

#### Adding Photos
- [ ] "Add Photos" button visible
- [ ] Shows count (0/4)
- [ ] File picker opens
- [ ] Can select multiple images
- [ ] Preview grid shows selected images
- [ ] Can remove images (X button on hover)
- [ ] Max 4 images enforced
- [ ] Button disables at 4 images

#### Posting
- [ ] "Post Update" button visible
- [ ] Disabled when content empty
- [ ] Enabled when content added
- [ ] Shows loading state when posting
- [ ] Success toast appears
- [ ] Modal closes on success
- [ ] New post appears in feed
- [ ] Feed scrolls to show new post

**Test**: Create post with text only, then with 1-4 images

---

### 5. Edit Update Flow

#### Opening Edit
- [ ] Click three-dot menu on post
- [ ] "Edit Post" option visible
- [ ] Click opens edit modal
- [ ] Modal shows current content
- [ ] Content is editable

#### Editing
- [ ] Can modify text
- [ ] Textarea shows existing content
- [ ] "Save Changes" button visible
- [ ] "Cancel" button works

#### Saving
- [ ] Disabled when no changes
- [ ] Enabled when content modified
- [ ] Shows loading state
- [ ] Success toast appears
- [ ] Modal closes
- [ ] Post updates in feed
- [ ] Timestamp doesn't change

**Test**: Edit post, save, verify changes persist

---

### 6. Delete Update Flow

#### Deleting
- [ ] Click three-dot menu
- [ ] "Delete Post" option visible (red)
- [ ] Confirmation dialog appears
- [ ] Can cancel deletion
- [ ] Can confirm deletion
- [ ] Success toast appears
- [ ] Post removed from feed
- [ ] Counts update

**Test**: Delete post with threads, verify children also deleted

---

### 7. Pin/Unpin Functionality

#### Pinning
- [ ] Click three-dot menu
- [ ] "Pin to Top" option visible
- [ ] Click pins post
- [ ] Post moves to top of feed
- [ ] "Pinned" badge appears
- [ ] Success toast shows

#### Unpinning
- [ ] Menu shows "Unpin from Top"
- [ ] Click unpins post
- [ ] Post returns to chronological order
- [ ] Badge removed
- [ ] Success toast shows

**Test**: Pin multiple posts, verify only one can be pinned

---

### 8. Threading Functionality

#### Creating Thread
- [ ] Click three-dot menu
- [ ] "Add to Thread" option visible
- [ ] Click shows thread input box
- [ ] Input appears below parent post
- [ ] Has primary border highlight
- [ ] Placeholder text shows
- [ ] Can type content

#### Thread Reply Box
- [ ] Shows "Adding to thread" label
- [ ] Has Cancel button
- [ ] Has "Post to Thread" button
- [ ] Cancel closes box
- [ ] Post creates threaded reply

#### Thread Display
- [ ] Child post indented (ml-12)
- [ ] Vertical line connector visible
- [ ] Line has gradient effect
- [ ] Child post has same styling
- [ ] Can interact with child post

**Test**: Create thread with 2-3 levels, verify nesting

---

### 9. Like Functionality

#### Liking
- [ ] Click Like button
- [ ] Heart icon fills red
- [ ] Like count increments
- [ ] Button shows "liked" state
- [ ] Scale animation plays
- [ ] No page reload

#### Unliking
- [ ] Click Like button again
- [ ] Heart icon unfills
- [ ] Like count decrements
- [ ] Button returns to normal
- [ ] Smooth transition

#### Count Display
- [ ] Shows accurate count
- [ ] Updates in real-time
- [ ] Clicking count toggles like
- [ ] Works on threaded posts

**Test**: Like/unlike multiple times, verify count accuracy

---

### 10. Comments Functionality

#### Opening Comments
- [ ] Click Comment button
- [ ] Comments section expands
- [ ] Separator line appears
- [ ] Loading spinner shows (if loading)
- [ ] Comments load and display

#### Comment Display
- [ ] Each comment has avatar
- [ ] Username shows
- [ ] Comment text displays
- [ ] Timestamp shows
- [ ] Delete button visible
- [ ] Rounded bubble design
- [ ] Hover effect works

#### Adding Comment
- [ ] Input field visible at bottom
- [ ] Placeholder text shows
- [ ] Can type comment
- [ ] Send button visible (circular)
- [ ] Send disabled when empty
- [ ] Press Enter to send
- [ ] Click send button to send

#### After Posting Comment
- [ ] Comment appears immediately
- [ ] Comment count increments
- [ ] Input clears
- [ ] Success toast shows
- [ ] Can add another comment

#### Deleting Comment
- [ ] Click Delete button
- [ ] Confirmation appears
- [ ] Can cancel
- [ ] Can confirm
- [ ] Comment removed
- [ ] Count decrements
- [ ] Success toast shows

**Test**: Add 5+ comments, verify scroll area works

---

### 11. Right Panel - Insights

#### Engagement Summary
- [ ] "This Month" card visible
- [ ] Shows total likes
- [ ] Shows total comments
- [ ] Shows total posts
- [ ] Counts are accurate
- [ ] Updates in real-time

#### Latest Activity
- [ ] Card visible
- [ ] Shows relevant message
- [ ] Message changes based on activity
- [ ] Icon displays correctly

#### Community Card
- [ ] Card visible
- [ ] Shows community message
- [ ] Icon displays correctly

#### Collapse/Expand
- [ ] Chevron button visible
- [ ] Click collapses panel
- [ ] Panel width animates to 12px
- [ ] Click expands panel
- [ ] Panel width animates to 280px
- [ ] Smooth transition

**Test**: Collapse/expand multiple times

---

### 12. Theme Support

#### Light Mode
- [ ] Background is white
- [ ] Panels are light gray (#f8f9fb)
- [ ] Text is dark
- [ ] Borders are subtle
- [ ] Cards have white background
- [ ] Proper contrast

#### Dark Mode
- [ ] Background is dark (#0a0f24)
- [ ] Panels are darker (#0e1a32)
- [ ] Text is light
- [ ] Borders are subtle
- [ ] Cards have dark background
- [ ] Proper contrast

#### Theme Toggle
- [ ] Switch to dark mode
- [ ] All colors update
- [ ] No flash of wrong theme
- [ ] Icons remain visible
- [ ] Hover states work
- [ ] Switch back to light mode
- [ ] Everything updates correctly

**Test**: Toggle theme multiple times, verify all elements adapt

---

### 13. Performance Testing

#### Load Time
- [ ] Page loads in < 2 seconds
- [ ] No layout shift
- [ ] Images load progressively
- [ ] Smooth initial render

#### Scrolling
- [ ] Smooth scroll in feed
- [ ] No janky animations
- [ ] Side panels stay fixed
- [ ] No lag with many posts

#### Interactions
- [ ] Buttons respond instantly
- [ ] Modals open smoothly
- [ ] No delay on like/comment
- [ ] Optimistic UI updates work

**Test**: Create 20+ posts, verify smooth scrolling

---

### 14. Error Handling

#### Network Errors
- [ ] Failed post shows error toast
- [ ] Failed like shows error toast
- [ ] Failed comment shows error toast
- [ ] Failed delete shows error toast
- [ ] Can retry failed actions

#### Validation Errors
- [ ] Empty post shows error
- [ ] Too many images shows error
- [ ] Invalid file type shows error
- [ ] Error messages are clear

**Test**: Disconnect network, try actions, verify error handling

---

### 15. Accessibility Testing

#### Keyboard Navigation
- [ ] Can tab through elements
- [ ] Focus visible on all interactive elements
- [ ] Can open modals with keyboard
- [ ] Can close modals with Escape
- [ ] Can submit forms with Enter

#### Screen Reader
- [ ] Buttons have proper labels
- [ ] Images have alt text
- [ ] Form inputs have labels
- [ ] Error messages announced
- [ ] Success messages announced

#### Color Contrast
- [ ] Text readable in light mode
- [ ] Text readable in dark mode
- [ ] Meets WCAG AA standards
- [ ] Icons have sufficient contrast

**Test**: Navigate with keyboard only, verify all features accessible

---

## Browser Compatibility

Test in:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

---

## Common Issues & Solutions

### Issue: Layout breaks on mobile
**Solution**: Check responsive classes (hidden lg:block)

### Issue: Images not loading
**Solution**: Verify VITE_API_URL environment variable

### Issue: Counts not updating
**Solution**: Check API responses, verify state updates

### Issue: Theme not switching
**Solution**: Verify ThemeProvider wraps component

### Issue: Modals not opening
**Solution**: Check Dialog state management

---

## Performance Benchmarks

**Target Metrics:**
- First Contentful Paint: < 1.5s
- Time to Interactive: < 3s
- Largest Contentful Paint: < 2.5s
- Cumulative Layout Shift: < 0.1
- First Input Delay: < 100ms

**Test with:**
- Chrome DevTools Lighthouse
- WebPageTest
- Real device testing

---

## Final Checklist

Before marking as complete:
- [ ] All features work as expected
- [ ] No console errors
- [ ] No TypeScript errors
- [ ] Responsive on all screen sizes
- [ ] Both themes work correctly
- [ ] Performance is acceptable
- [ ] Accessibility standards met
- [ ] Error handling works
- [ ] Loading states show
- [ ] Success/error toasts appear
- [ ] Code is clean and commented
- [ ] No unused imports
- [ ] Proper type safety

---

## Reporting Issues

When reporting bugs, include:
1. Browser & version
2. Screen size
3. Steps to reproduce
4. Expected behavior
5. Actual behavior
6. Screenshots/video
7. Console errors
8. Network tab (if API related)

---

**Happy Testing! 🚀**
