# Settings Page Testing Guide

## Quick Start

1. **Start the development server:**
   ```bash
   cd capstone_frontend
   npm run dev
   ```

2. **Login as a charity admin**
   - Navigate to `/auth/login`
   - Use charity admin credentials

3. **Access Settings Page:**
   - **Option 1:** Click user icon (top-right) → "Settings"
   - **Option 2:** Click "More" dropdown → "Settings"
   - **Direct URL:** `/charity/settings`

## Test Each Section

### 1. Account Settings ⚙️
**What to Test:**
- [ ] Organization name field is editable
- [ ] Email, phone, address fields work
- [ ] Website URL field accepts valid URLs
- [ ] Timezone dropdown shows options
- [ ] Language dropdown shows options
- [ ] Save button shows loading state
- [ ] Success toast appears after save

**Test Data:**
```
Organization: Test Charity Foundation
Email: contact@testcharity.org
Phone: +63 912 345 6789
Address: 123 Main St, Manila, Philippines
Website: https://testcharity.org
Timezone: UTC+8 (Philippine Time)
Language: English
```

### 2. Security & Access Control 🔒
**What to Test:**
- [ ] "Change Password" button opens modal
- [ ] Password modal has 3 fields (current, new, confirm)
- [ ] Password validation works (min 8 chars)
- [ ] Passwords must match
- [ ] 2FA toggle switches on/off
- [ ] Success message when 2FA enabled
- [ ] Login activity list displays
- [ ] Current session is marked

**Test Scenarios:**
1. Try changing password with mismatched passwords (should fail)
2. Try password < 8 characters (should fail)
3. Toggle 2FA on and off
4. Check login activity shows recent sessions

### 3. Notifications 🔔
**What to Test:**
- [ ] All 8 toggle switches work
- [ ] Email Notifications toggle
- [ ] Donation Alerts toggle
- [ ] Campaign Updates toggle
- [ ] Platform Announcements toggle
- [ ] Volunteer Sign-ups toggle
- [ ] Comments toggle
- [ ] Monthly Reports toggle
- [ ] Weekly Digest toggle
- [ ] Save button updates preferences
- [ ] Success toast appears

**Test Flow:**
1. Toggle all switches ON
2. Click Save
3. Refresh page (settings should persist)
4. Toggle some switches OFF
5. Save again

### 4. Integrations 🔗
**What to Test:**
- [ ] Facebook connect/disconnect button
- [ ] Twitter connect/disconnect button
- [ ] Instagram connect/disconnect button
- [ ] Payment Gateway configure button
- [ ] Connected badge appears when connected
- [ ] API key fields accept input
- [ ] Save button works

**Test Flow:**
1. Connect Facebook (button changes to "Disconnect")
2. Check green "Connected" badge appears
3. Disconnect Facebook
4. Try other social platforms
5. Enter test API keys
6. Save settings

### 5. Privacy & Data 👁️
**What to Test:**
- [ ] Profile visibility radio buttons (Public/Registered/Private)
- [ ] Show Donor List toggle
- [ ] Show Campaign Stats toggle
- [ ] Show Team Members toggle
- [ ] Allow Comments toggle
- [ ] Email visibility radio buttons
- [ ] Save button works
- [ ] Alert message displays for private profiles

**Test Scenarios:**
1. Set profile to "Public" → Save
2. Set profile to "Private" → Check alert appears
3. Toggle donor list visibility
4. Change email visibility settings
5. Save and verify

### 6. Danger Zone ⚠️
**What to Test:**
- [ ] Deactivate button opens warning modal
- [ ] Deactivate modal shows consequences
- [ ] Delete button opens confirmation modal
- [ ] Delete requires typing "DELETE"
- [ ] Delete button disabled until "DELETE" typed
- [ ] Cancel buttons work
- [ ] Modals close properly

**⚠️ WARNING: Don't actually delete/deactivate unless testing with dummy account!**

**Test Flow:**
1. Click "Deactivate Organization"
2. Read warning modal
3. Click Cancel (don't actually deactivate)
4. Click "Delete Account Permanently"
5. Try clicking Delete without typing "DELETE" (should be disabled)
6. Type "DELETE" in field
7. Click Cancel (don't actually delete)

## Visual/UI Tests

### Sidebar Navigation
- [ ] Sidebar is sticky (stays visible when scrolling)
- [ ] Active section is highlighted
- [ ] Icons display correctly
- [ ] Danger Zone shows in red
- [ ] Hover effects work on sidebar items
- [ ] Clicking sidebar items changes content

### Responsive Design
- [ ] Test on desktop (1920px)
- [ ] Test on tablet (768px)
- [ ] Test on mobile (375px)
- [ ] Sidebar collapses on mobile
- [ ] Content is readable on all sizes

### Dark Mode
- [ ] Toggle theme to dark mode
- [ ] All sections render correctly
- [ ] Gold accents visible
- [ ] Text is readable
- [ ] Cards have proper contrast
- [ ] Danger zone still shows red

### Animations & Interactions
- [ ] Toggle switches animate smoothly
- [ ] Buttons show hover effects
- [ ] Modals fade in/out
- [ ] Toast notifications slide in
- [ ] Loading states show on save
- [ ] Form inputs focus properly

## Organization Profile Verification

### Check Organization Profile Page
1. Navigate to `/charity/organization/manage`
2. Verify tabs are: **Overview | About | Team | Media | Campaigns**
3. Confirm **NO Settings tab** present
4. Check tab count is 5 (not 6)

## Navbar Verification

### Check Navbar Links
1. **User Dropdown:**
   - [ ] Click user icon (top-right)
   - [ ] "Settings" option present
   - [ ] Clicking opens `/charity/settings`

2. **More Dropdown:**
   - [ ] Click "More" in navbar
   - [ ] "Settings" option present
   - [ ] Clicking opens `/charity/settings`

## Common Issues & Solutions

### Issue: Settings page shows blank
**Solution:** Check browser console for errors, verify all section components are imported

### Issue: Toggles don't switch
**Solution:** Check state management, verify onChange handlers

### Issue: Save button doesn't work
**Solution:** Check API endpoints are configured, verify toast notifications

### Issue: Modals don't open
**Solution:** Check Dialog component imports, verify state variables

### Issue: Sidebar not sticky
**Solution:** Check CSS classes, verify `sticky top-20` is applied

## Performance Checks

- [ ] Page loads in < 2 seconds
- [ ] No console errors
- [ ] No console warnings
- [ ] Smooth scrolling
- [ ] No layout shifts
- [ ] Images load properly
- [ ] Forms are responsive

## Accessibility Tests

- [ ] Tab navigation works
- [ ] Focus indicators visible
- [ ] Labels associated with inputs
- [ ] Buttons have descriptive text
- [ ] Color contrast is sufficient
- [ ] Screen reader friendly

## Browser Compatibility

Test in:
- [ ] Chrome/Edge (Chromium)
- [ ] Firefox
- [ ] Safari (if available)
- [ ] Mobile browsers

## Final Checklist

- [ ] All 6 sections load correctly
- [ ] All forms accept input
- [ ] All toggles work
- [ ] All buttons function
- [ ] All modals open/close
- [ ] Toast notifications appear
- [ ] Dark mode works
- [ ] Responsive on all sizes
- [ ] No console errors
- [ ] Settings tab removed from Org Profile
- [ ] Navbar links work

## Success Criteria

✅ **Settings page is fully functional**
✅ **All sections render without errors**
✅ **User can navigate between sections**
✅ **Forms and toggles work as expected**
✅ **Modals and confirmations function properly**
✅ **Organization Profile has only 5 tabs**
✅ **Settings accessible from navbar**

---

**If all tests pass:** Settings restructure is complete! 🎉
**If issues found:** Document them and fix before deployment
