# Quick Start - Testing the Updated Charity Card

## 🚀 How to Test

### Step 1: Start the Development Server

```bash
cd capstone_frontend
npm run dev
```

The app should start at `http://localhost:5173` (or similar).

---

### Step 2: Navigate to the Charities Page

1. Open your browser
2. Go to: `http://localhost:5173/donor/charities`
3. You should see the updated charity cards in a grid layout

---

### Step 3: Test Visual Features

#### ✅ Image Hover Effect
1. **Hover over any charity image**
   - Image should zoom in smoothly
   - Dark overlay should appear
   - "View Profile" text with eye icon should show in center
   - Animation should be smooth (0.5s)

#### ✅ Card Hover Effect
1. **Hover over the card**
   - Card shadow should expand
   - Subtle elevation effect
   - Smooth transition

#### ✅ Featured Badge
1. **Look for charities with >₱100K raised**
   - Should show gold gradient "Featured" badge
   - Badge in top-left corner with star icon

#### ✅ Verification Badge
1. **Look for verified charities**
   - Green "Verified" badge in top-right
   - Checkmark icon
   - Hover over it to see tooltip

---

### Step 4: Test Clickable Elements

#### ✅ Charity Image Click
1. **Click on the charity image**
   - Should navigate to `/donor/charities/{id}`
   - Charity profile page should load

#### ✅ Charity Name Click
1. **Click on the charity name**
   - Should navigate to charity profile
   - Name should turn primary color on hover

#### ✅ Card Background Click
1. **Click anywhere on the card (not on buttons)**
   - Should navigate to charity profile

---

### Step 5: Test Buttons

#### ✅ Donate Button
1. **Click the "Donate" button**
   - Should navigate to `/donor/donate/{id}`
   - Button should have glow effect on hover
   - Heart icon should be visible

#### ✅ Follow/Unfollow Button
1. **Click the Follow button (+ icon)**
   - Should show loading state briefly
   - Icon should change to minus (-)
   - Follower count should increase by 1
   - Toast notification should appear
   - Button should turn solid primary color

2. **Click Unfollow button (- icon)**
   - Should toggle back
   - Follower count should decrease by 1
   - Button should return to outline style
   - Toast notification should appear

#### ✅ View Button
1. **Click the View button (eye icon)**
   - Should navigate to charity profile
   - Button should have hover effect

---

### Step 6: Test Data Display

#### ✅ Stats Grid
1. **Check the three stats displayed:**
   - **Followers**: Should show count with Users icon
   - **Campaigns**: Should show count with Target icon
   - **Total Raised**: Should show amount with TrendingUp icon

2. **Hover over each stat**
   - Tooltip should appear with full details
   - Example: "2,450 followers" instead of "2.5K"

#### ✅ Number Formatting
1. **Check large numbers:**
   - 1,000+ should show as "1.0K"
   - 1,000,000+ should show as "1.0M"
   - Under 1,000 should show full number

#### ✅ Social Proof
1. **For charities with 100+ followers:**
   - Should show "Supported by X donors" at bottom
   - Text should be centered and muted

---

### Step 7: Test Responsive Design

#### ✅ Desktop View (>1024px)
1. **Resize browser to full width**
   - Should show 3 columns
   - Cards should be evenly spaced
   - All elements visible

#### ✅ Tablet View (768px - 1024px)
1. **Resize browser to ~900px width**
   - Should show 2 columns
   - Layout should adjust smoothly

#### ✅ Mobile View (<768px)
1. **Resize browser to ~400px width**
   - Should show 1 column
   - Cards should stack vertically
   - All buttons should remain accessible

---

### Step 8: Test Edge Cases

#### ✅ No Image
1. **Find charity without logo**
   - Should show placeholder image
   - Hover effects should still work

#### ✅ Long Charity Name
1. **Find charity with long name**
   - Name should truncate with ellipsis
   - Full name visible on hover (if tooltip added)

#### ✅ Long Description
1. **Check mission descriptions**
   - Should truncate to 2 lines
   - Ellipsis (...) should appear

#### ✅ Zero Stats
1. **Check new charities**
   - Should display "0" for zero values
   - No errors or blank spaces

#### ✅ Not Logged In
1. **Log out and try to follow**
   - Should show error toast
   - "Please login to follow charities"

---

### Step 9: Test Performance

#### ✅ Loading State
1. **Refresh the page**
   - Should show skeleton cards while loading
   - 6 animated placeholder cards
   - Smooth transition to real cards

#### ✅ Image Loading
1. **Check network tab**
   - Images should lazy load
   - Not all images load at once

#### ✅ API Calls
1. **Open browser DevTools → Network**
   - Should see API calls for:
     - Charities list
     - Follow status
     - Followers count
     - Campaigns
     - Charity details

---

### Step 10: Test Navigation Flow

#### ✅ Complete User Journey
1. **Start at Browse Charities**
2. **Click on a charity card**
   - Navigate to Charity Profile
3. **Check profile page shows:**
   - About tab with mission/vision
   - Updates tab with posts
   - Campaigns tab with active campaigns
4. **Click back button**
   - Return to Browse Charities
5. **Click Donate on a card**
   - Navigate to Donation page
   - Charity should be pre-selected

---

## 🐛 Common Issues & Solutions

### Issue: Images not loading
**Solution**: Check `VITE_API_URL` in `.env` file
```bash
VITE_API_URL=http://localhost:8000
```

### Issue: Follow button not working
**Solution**: 
1. Check if logged in as donor
2. Check backend API is running
3. Check browser console for errors

### Issue: Stats showing 0 for all charities
**Solution**:
1. Check API endpoints are accessible
2. Verify database has data
3. Check network tab for failed requests

### Issue: Hover effects not smooth
**Solution**:
1. Check browser supports CSS transitions
2. Try disabling browser extensions
3. Check GPU acceleration is enabled

### Issue: Cards not responsive
**Solution**:
1. Check Tailwind CSS is loaded
2. Verify breakpoints in browser DevTools
3. Clear browser cache

---

## 📊 Expected Results

### Visual Checklist:
- ✅ Cards display in grid (1/2/3 columns based on screen)
- ✅ Images load with proper aspect ratio
- ✅ Hover effects are smooth and performant
- ✅ All icons display correctly
- ✅ Text is readable and properly aligned
- ✅ Badges show for verified/featured charities
- ✅ Stats grid is evenly spaced
- ✅ Buttons are properly styled

### Functional Checklist:
- ✅ All clickable elements navigate correctly
- ✅ Follow/unfollow updates state immediately
- ✅ Follower count updates on toggle
- ✅ Toast notifications appear
- ✅ Loading states show during API calls
- ✅ Error handling works (try without login)
- ✅ Tooltips appear on hover
- ✅ Responsive layout works on all sizes

### Performance Checklist:
- ✅ Page loads in <2 seconds
- ✅ Images lazy load
- ✅ No layout shift during load
- ✅ Smooth 60fps animations
- ✅ No console errors
- ✅ API calls are efficient

---

## 🎯 Success Criteria

Your implementation is successful if:

1. **All visual features work** as described in requirements
2. **All interactive elements** respond correctly
3. **Navigation flows** work end-to-end
4. **Responsive design** adapts to all screen sizes
5. **Performance** is smooth with no lag
6. **No console errors** during normal usage
7. **Backend sync** works (follow state persists)
8. **Edge cases** are handled gracefully

---

## 📸 Screenshots to Take

For documentation, capture:

1. **Desktop view** - 3 column grid
2. **Tablet view** - 2 column grid
3. **Mobile view** - 1 column stack
4. **Hover state** - Image zoom with overlay
5. **Featured badge** - Gold badge on card
6. **Verified badge** - Green badge with tooltip
7. **Stats grid** - All three stats visible
8. **Follow state** - Before and after toggle
9. **Loading state** - Skeleton cards
10. **Empty state** - No charities found

---

## 🔍 Browser Testing

Test in these browsers:

- ✅ **Chrome** (latest)
- ✅ **Firefox** (latest)
- ✅ **Safari** (latest)
- ✅ **Edge** (latest)
- ✅ **Mobile Safari** (iOS)
- ✅ **Chrome Mobile** (Android)

---

## 📝 Testing Checklist

Print this and check off as you test:

### Visual Tests
- [ ] Image hover zoom works
- [ ] Overlay appears on hover
- [ ] Card shadow expands on hover
- [ ] Featured badge shows correctly
- [ ] Verified badge shows correctly
- [ ] Stats grid displays properly
- [ ] Buttons styled correctly
- [ ] Text truncates properly

### Interaction Tests
- [ ] Image click navigates
- [ ] Name click navigates
- [ ] Card click navigates
- [ ] Donate button works
- [ ] Follow button toggles
- [ ] Unfollow button toggles
- [ ] View button navigates
- [ ] Tooltips appear

### Data Tests
- [ ] Follower count displays
- [ ] Campaign count displays
- [ ] Total raised displays
- [ ] Numbers format correctly
- [ ] Location shows
- [ ] Category badge appears
- [ ] Social proof shows (>100 followers)

### Responsive Tests
- [ ] Desktop: 3 columns
- [ ] Tablet: 2 columns
- [ ] Mobile: 1 column
- [ ] All elements accessible
- [ ] No horizontal scroll

### Edge Case Tests
- [ ] No image: placeholder shows
- [ ] Long name: truncates
- [ ] Long description: truncates
- [ ] Zero stats: shows 0
- [ ] Not logged in: error message
- [ ] No category: badge hidden

### Performance Tests
- [ ] Page loads quickly
- [ ] Images lazy load
- [ ] Animations smooth
- [ ] No console errors
- [ ] API calls efficient

---

## 🎉 Next Steps

After testing:

1. **Document any bugs** found
2. **Take screenshots** of working features
3. **Note performance metrics**
4. **Test on real devices** (not just browser resize)
5. **Get user feedback** from team
6. **Deploy to staging** environment
7. **Run automated tests** (if available)
8. **Update documentation** with findings

---

## 🆘 Need Help?

### Resources:
- **Component Code**: `src/components/donor/CharityCard.tsx`
- **Page Code**: `src/pages/donor/BrowseCharities.tsx`
- **Summary Doc**: `CHARITY_CARD_UPDATE_SUMMARY.md`
- **Features Doc**: `CHARITY_CARD_FEATURES.md`

### Debug Tips:
1. Open browser DevTools (F12)
2. Check Console for errors
3. Check Network tab for API calls
4. Use React DevTools to inspect state
5. Check Elements tab for CSS issues

---

**Happy Testing! 🚀**

Last Updated: 2025-01-16
