# Donation Management - Quick Start Guide

## 🚀 Getting Started

### 1. Start the Development Server
```bash
cd capstone_frontend
npm run dev
```

### 2. Login as Charity Admin
- Navigate to `/auth/login`
- Use charity admin credentials
- You'll be redirected to the charity dashboard

### 3. Access Donation Management
- Click "Donations" in the sidebar
- Or navigate directly to `/charity/donations`

---

## 📋 Component Overview

### Main Components Created
```
✅ DonationsPage.tsx          - Main container & state management
✅ DonationsTable.tsx          - Advanced data table
✅ DonationFilters.tsx         - Search & filters
✅ DonationDetailsModal.tsx    - View/manage donations
✅ DonationStatsSidebar.tsx    - KPIs & charts
✅ ReconciliationModal.tsx     - Transaction matching
✅ ExportMenu.tsx              - Export & bulk actions
```

---

## 🎯 Key Features to Test

### 1. **View Donations**
- Table displays all donations
- Color-coded status badges:
  - 🟡 Pending (amber)
  - 🟢 Completed (green)
  - 🔴 Rejected (red)

### 2. **Search & Filter**
- **Search bar:** Type donor name or transaction ID
- **Status dropdown:** Filter by pending/completed/rejected
- **Advanced filters:** Click "Advanced" button
  - Date range picker
  - Amount range
  - Payment method
  - Quick presets (Today, Last 7 Days, etc.)

### 3. **Sort Table**
- Click any column header to sort
- Click again to reverse order
- Sortable columns:
  - Donor name
  - Amount
  - Date & Time
  - Status

### 4. **View Details**
- Click the 👁️ (eye) icon on any row
- Modal shows:
  - Full donor information
  - Donation details
  - Proof of payment
  - Admin notes section

### 5. **Confirm Donation**
- For pending donations, click ✅ (checkmark) icon
- Or open details modal and click "Confirm Donation"
- Status changes to "Completed"

### 6. **Reject Donation**
- Click ❌ (X) icon on pending donation
- Enter rejection reason (required)
- Click "Confirm Rejection"
- Status changes to "Rejected"

### 7. **Bulk Actions**
- Select multiple donations using checkboxes
- Click "Export & Actions" button
- Choose:
  - Confirm Selected
  - Reject Selected
  - Export Selected

### 8. **View Statistics**
- Right sidebar shows:
  - Total received (all time)
  - This month total
  - Pending/Confirmed/Rejected counts
  - Average donation amount
  - Last 7 days chart
  - Payment method distribution

### 9. **Reconciliation**
- Click "Run Reconciliation" in sidebar
- Match pending donations with bank transactions
- Select donation (left column)
- Select transaction (right column)
- Click "Create Match"
- Review matched pairs
- Click "Apply Reconciliation"

### 10. **Export Data**
- Click "Export & Actions" dropdown
- Choose format:
  - CSV
  - Excel
  - PDF
- File downloads with current filters applied

---

## 🎨 Visual Guide

### Page Layout
```
┌─────────────────────────────────────────────────────┐
│ Donation Management          [Export] [+ Log New]   │
├─────────────────────────────────────────────────────┤
│                                                     │
│ ┌─────────────────────────┐  ┌──────────────────┐ │
│ │ Filters & Search        │  │                  │ │
│ │ [Search...] [Status ▼]  │  │  KPI Sidebar     │ │
│ │ [Advanced]              │  │                  │ │
│ └─────────────────────────┘  │  📊 Stats        │ │
│                              │  📈 Chart        │ │
│ ┌─────────────────────────┐  │  💳 Methods      │ │
│ │                         │  │  ⚡ Actions      │ │
│ │  Donations Table        │  │                  │ │
│ │  with sorting,          │  │                  │ │
│ │  pagination,            │  │                  │ │
│ │  and actions            │  │                  │ │
│ │                         │  │                  │ │
│ └─────────────────────────┘  └──────────────────┘ │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### Table Structure
```
☑ | ID     | Donor      | Campaign | Amount  | Date       | Method | Status    | Actions
──┼────────┼────────────┼──────────┼─────────┼────────────┼────────┼───────────┼─────────
☐ | #00001 | John Doe   | Relief   | ₱5,000  | Oct 15     | GCash  | 🟡 Pending | 👁️ ✅ ❌
☐ | #00002 | Anonymous  | General  | ₱10,000 | Oct 14     | Bank   | 🟢 Done    | 👁️
☐ | #00003 | Jane Smith | Medical  | ₱2,500  | Oct 13     | PayPal | 🔴 Reject  | 👁️
```

---

## 🔧 Common Tasks

### Task 1: Review Today's Donations
```
1. Click "Advanced" filter button
2. Click "Today" preset
3. Review list
4. Confirm valid donations
5. Reject suspicious ones
```

### Task 2: Find Specific Donation
```
1. Type donor name in search bar
2. Or type transaction ID
3. Results filter automatically
4. Click "View" to see details
```

### Task 3: Bulk Confirm Pending
```
1. Filter by "Pending" status
2. Select all valid donations (checkboxes)
3. Click "Export & Actions"
4. Select "Confirm Selected"
5. Confirm action
```

### Task 4: Export Monthly Report
```
1. Click "Advanced" filters
2. Click "Last 30 Days" preset
3. Select "Completed" status
4. Click "Export & Actions"
5. Choose "Export as Excel"
6. Open downloaded file
```

### Task 5: Match Bank Transactions
```
1. Click "Run Reconciliation" in sidebar
2. Review pending donations (left)
3. Review bank transactions (right)
4. Match amounts and dates
5. Select matching pairs
6. Click "Create Match"
7. Repeat for all matches
8. Click "Apply Reconciliation"
```

---

## 🐛 Troubleshooting

### Issue: Page shows "No donations found"
**Solution:**
- Check if charity has any donations in database
- Verify API endpoint is working
- Check browser console for errors
- Try refreshing the page

### Issue: Filters not working
**Solution:**
- Clear all filters and try again
- Check if search term is correct
- Verify date range is valid
- Refresh the page

### Issue: Can't confirm donation
**Solution:**
- Check if donation is in "pending" status
- Verify you have admin permissions
- Check browser console for API errors
- Try logging out and back in

### Issue: Stats not updating
**Solution:**
- Click "Refresh Data" in sidebar
- Check if donations are loading
- Verify API connection
- Hard refresh browser (Ctrl+Shift+R)

### Issue: Modal not opening
**Solution:**
- Check browser console for errors
- Try clicking a different row
- Refresh the page
- Clear browser cache

---

## 📊 Understanding the Stats

### Total Received
- Sum of all **completed** donations (all time)
- Does not include pending or rejected

### This Month
- Sum of completed donations in current calendar month
- Resets on 1st of each month

### Pending Count
- Number of donations awaiting review
- These need admin action

### Average Donation
- Total amount ÷ total count
- Includes all statuses

### Last 7 Days Chart
- Shows daily confirmed donations
- Bars represent count per day
- Hover to see exact number

### Payment Methods
- Breakdown by proof_type field
- Shows percentage of total
- Progress bars visualize distribution

---

## 🎯 Best Practices

### Daily Workflow
1. **Morning:** Review pending donations
2. **Check:** Verify proof of payment
3. **Confirm:** Valid donations immediately
4. **Reject:** Suspicious ones with clear reason
5. **Reconcile:** Match with bank statements
6. **Export:** Daily report for accounting

### Weekly Tasks
- Run reconciliation for all pending
- Export weekly report
- Review rejection reasons
- Check for patterns in fraud attempts

### Monthly Tasks
- Export monthly report for accounting
- Review payment method trends
- Analyze average donation changes
- Update policies if needed

---

## 🔐 Security Notes

### Access Control
- Only charity admins can access this page
- All actions are logged (audit trail)
- Sensitive donor info protected
- Anonymous donations hide personal data

### Data Protection
- Proof of payment files are secured
- Admin notes not visible to donors
- Export files should be handled securely
- Don't share transaction IDs publicly

---

## 📞 Support

### Need Help?
- Check the full documentation: `DONATION_MANAGEMENT_COMPLETE.md`
- Contact support via "Contact Support" link in sidebar
- Review donation policy guidelines
- Check accounting export instructions

---

## ✅ Quick Checklist

Before going live, verify:
- [ ] All donations load correctly
- [ ] Filters work as expected
- [ ] Sorting functions properly
- [ ] Confirm/Reject actions work
- [ ] Modals open and close
- [ ] Stats calculate correctly
- [ ] Charts render properly
- [ ] Export generates files
- [ ] Reconciliation matches work
- [ ] Responsive on mobile
- [ ] Dark mode looks good
- [ ] No console errors

---

## 🚀 You're Ready!

The Donation Management system is now fully functional. Start by:
1. Reviewing pending donations
2. Confirming valid ones
3. Exploring the filters
4. Checking the stats
5. Running a test export

**Happy managing! 🎉**
