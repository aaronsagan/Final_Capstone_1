# 🔍 Campaign Overview Tab - Debugging Guide

## 🚨 **Issue**
The **Top Campaigns** and **Donation Growth Over Time** sections in the Campaign Overview tab are not displaying data, even though donations exist in the backend.

---

## 📊 **Root Cause Analysis**

The Campaign Overview tab depends on these **2 critical API endpoints**:

1. **`/analytics/campaigns/top-performers`** → Powers "Top Campaigns" section
2. **`/analytics/campaigns/temporal`** → Powers "Donation Growth Over Time" section

**Most likely cause**: These endpoints are **not yet implemented** or are returning empty/incorrect data structures.

---

## 🔎 **Step 1: Check Browser Console**

I've added console logging to help debug. Open your browser DevTools (F12) and check for these messages:

```
🏆 Top performers data loaded: {...}
📈 Temporal trends data loaded: {...}
```

### **What to Look For**:

**Case 1: Data is Empty**
```javascript
🏆 Top performers data loaded: { data: [] }  // ❌ Empty
📈 Temporal trends data loaded: { data: [] }  // ❌ Empty
```
**Solution**: Backend endpoints need to be implemented (see Step 2)

**Case 2: Data Structure Mismatch**
```javascript
🏆 Top performers data loaded: { campaigns: [...] }  // ❌ Wrong key
// Frontend expects: { data: [...] }
```
**Solution**: Backend response format needs adjustment

**Case 3: Network Error**
```
Failed to fetch /analytics/campaigns/top-performers: 404 Not Found
```
**Solution**: Endpoints don't exist yet (see Step 2)

---

## 🛠️ **Step 2: Backend Endpoint Requirements**

### **Endpoint 1: Top Performers**

**URL**: `GET /analytics/campaigns/top-performers`

**Query Parameters**:
- `charity_id` (optional) - Filter by specific charity

**Expected Response**:
```json
{
  "data": [
    {
      "id": 1,
      "title": "Medical Drive 2025",
      "campaign_type": "medical",
      "raised_amount": 45000,
      "goal_amount": 50000,
      "progress": 90.0,
      "donation_count": 12,
      "status": "active"
    },
    {
      "id": 2,
      "title": "School Supply Fund",
      "campaign_type": "education",
      "raised_amount": 30000,
      "goal_amount": 40000,
      "progress": 75.0,
      "donation_count": 8,
      "status": "active"
    }
  ]
}
```

**SQL Query Example** (Python/SQLAlchemy):
```python
@router.get("/analytics/campaigns/top-performers")
async def get_top_performers(
    charity_id: Optional[int] = None,
    limit: int = 5,
    db: Session = Depends(get_db)
):
    query = db.query(
        Campaign.id,
        Campaign.title,
        Campaign.campaign_type,
        Campaign.goal_amount,
        Campaign.status,
        func.coalesce(func.sum(Donation.amount), 0).label('raised_amount'),
        func.count(Donation.id).label('donation_count')
    ).outerjoin(Donation, and_(
        Donation.campaign_id == Campaign.id,
        Donation.status == 'verified'  # Only count verified donations
    )).group_by(Campaign.id)
    
    if charity_id:
        query = query.filter(Campaign.charity_id == charity_id)
    
    campaigns = query.order_by(desc('raised_amount')).limit(limit).all()
    
    # Calculate progress percentage
    result = []
    for campaign in campaigns:
        progress = (campaign.raised_amount / campaign.goal_amount * 100) if campaign.goal_amount > 0 else 0
        result.append({
            "id": campaign.id,
            "title": campaign.title,
            "campaign_type": campaign.campaign_type,
            "raised_amount": float(campaign.raised_amount),
            "goal_amount": float(campaign.goal_amount),
            "progress": round(progress, 1),
            "donation_count": campaign.donation_count,
            "status": campaign.status
        })
    
    return {"data": result}
```

---

### **Endpoint 2: Temporal Trends**

**URL**: `GET /analytics/campaigns/temporal`

**Query Parameters**:
- `charity_id` (optional) - Filter by specific charity
- `period` (optional) - Aggregation period: `day`, `week`, `month` (default: `month`)

**Expected Response**:
```json
{
  "data": [
    {
      "period": "Jan",
      "total_amount": 25000,
      "campaign_count": 3,
      "donation_count": 8
    },
    {
      "period": "Feb",
      "total_amount": 35000,
      "campaign_count": 4,
      "donation_count": 12
    },
    {
      "period": "Mar",
      "total_amount": 50000,
      "campaign_count": 5,
      "donation_count": 15
    }
  ]
}
```

**SQL Query Example** (Python/SQLAlchemy):
```python
@router.get("/analytics/campaigns/temporal")
async def get_temporal_trends(
    charity_id: Optional[int] = None,
    period: str = "month",
    db: Session = Depends(get_db)
):
    # Determine date truncation based on period
    if period == "day":
        date_trunc = func.date(Donation.created_at)
        format_str = "%d %b"
    elif period == "week":
        date_trunc = func.date_trunc('week', Donation.created_at)
        format_str = "Week %W"
    else:  # month
        date_trunc = func.date_trunc('month', Donation.created_at)
        format_str = "%b"
    
    query = db.query(
        func.to_char(date_trunc, format_str).label('period'),
        func.sum(Donation.amount).label('total_amount'),
        func.count(func.distinct(Donation.campaign_id)).label('campaign_count'),
        func.count(Donation.id).label('donation_count')
    ).join(Campaign).filter(
        Donation.status == 'verified'  # Only verified donations
    )
    
    if charity_id:
        query = query.filter(Campaign.charity_id == charity_id)
    
    trends = query.group_by('period').order_by('period').all()
    
    return {
        "data": [
            {
                "period": t.period,
                "total_amount": float(t.total_amount),
                "campaign_count": t.campaign_count,
                "donation_count": t.donation_count
            }
            for t in trends
        ]
    }
```

---

## 🔧 **Step 3: Quick Fix for Testing**

If you want to test the UI while the backend is being built, you can temporarily mock the data:

### **Option A: Mock in Frontend (Temporary)**

Edit `Analytics.tsx` around line 159:

```typescript
if (performersRes.ok) {
  const performersData = await performersRes.json();
  setTopPerformers(performersData.data || []);
  console.log('🏆 Top performers data loaded:', performersData);
} else {
  // TEMPORARY: Mock data for testing UI
  setTopPerformers([
    {
      id: 1,
      title: "Medical Drive 2025",
      campaign_type: "medical",
      raised_amount: 45000,
      goal_amount: 50000,
      progress: 90,
      donation_count: 12
    },
    {
      id: 2,
      title: "School Supply Fund",
      campaign_type: "education",
      raised_amount: 30000,
      goal_amount: 40000,
      progress: 75,
      donation_count: 8
    }
  ]);
  console.log('⚠️ Using mock top performers data');
}
```

And around line 149:

```typescript
if (trendsRes.ok) {
  const trendsData = await trendsRes.json();
  setTemporalTrends(trendsData.data || []);
  console.log('📈 Temporal trends data loaded:', trendsData);
} else {
  // TEMPORARY: Mock data for testing UI
  setTemporalTrends([
    { period: "Jan", total_amount: 25000, campaign_count: 3, donation_count: 8 },
    { period: "Feb", total_amount: 35000, campaign_count: 4, donation_count: 12 },
    { period: "Mar", total_amount: 50000, campaign_count: 5, donation_count: 15 },
    { period: "Apr", total_amount: 45000, campaign_count: 5, donation_count: 14 }
  ]);
  console.log('⚠️ Using mock temporal trends data');
}
```

**Remember to remove this mock data once the backend is ready!**

---

## 📋 **Step 4: Verify Other Working Sections**

These sections **should be working** if you have donations:

✅ **Campaign Types Distribution** - Uses `/analytics/campaigns/types`
✅ **Beneficiary Breakdown** - Uses `/analytics/campaigns/beneficiaries`
✅ **Top Campaign Highlight** - Uses `/analytics/overview`

If these also aren't working, check console logs for those endpoints too.

---

## 🎯 **Action Items**

### **For Frontend Developer**:
1. ✅ Console logs added - check browser console
2. ⏳ Verify data structure matches expected format
3. ⏳ Temporarily mock data (optional, for UI testing)

### **For Backend Developer**:
1. ⏳ Implement `/analytics/campaigns/top-performers` endpoint
2. ⏳ Implement `/analytics/campaigns/temporal` endpoint
3. ⏳ Ensure both return `{ data: [...] }` format
4. ⏳ Test with actual donation data
5. ⏳ Filter by `charity_id` when provided
6. ⏳ Only count `verified` donations

---

## 🧪 **Testing Checklist**

After backend implementation:

- [ ] `/analytics/campaigns/top-performers` returns campaigns sorted by raised amount
- [ ] Response includes `raised_amount`, `goal_amount`, `progress`, `donation_count`
- [ ] `/analytics/campaigns/temporal` returns monthly aggregated data
- [ ] Response includes `period`, `total_amount`, `campaign_count`, `donation_count`
- [ ] Both endpoints filter by `charity_id` when provided
- [ ] Browser console shows data with arrays (not empty)
- [ ] Top Campaigns section displays campaign cards
- [ ] Donation Growth chart renders line graph
- [ ] No empty state messages appear when data exists

---

## 📞 **Need Help?**

**Console showing empty data?**
→ Backend endpoints need implementation (see Step 2)

**Console showing 404 errors?**
→ Endpoints don't exist yet (see Step 2)

**Console showing wrong data structure?**
→ Response format needs to match expected structure

**UI still not showing data?**
→ Check that `temporalTrends.length > 0` and `topPerformers.length > 0` in console

---

## 🎉 **Expected Result**

Once working, you should see:

1. **Top Campaigns Section**:
   - Ranked list of 5 campaigns
   - Green numbered badges
   - Raised amount and progress %
   - Hover effects

2. **Donation Growth Chart**:
   - Line chart with monthly data points
   - Cyan colored line
   - Y-axis showing amounts in ₱K format
   - Tooltip on hover

Both sections with embedded insights at the bottom! 📊
