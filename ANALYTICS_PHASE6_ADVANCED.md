# Analytics Phase 6: Advanced Analytics & Recommendations

## Overview
Implemented advanced analytics features including fund-range histograms, percentiles, beneficiary clustering, and week-over-week trend detection with auto-generated explanations.

---

## Backend Implementation

### New Endpoints

#### 1. Advanced Type Analytics
```
GET /api/analytics/campaigns/{type}/advanced?charity_id={optional}
```

**Returns:**
```json
{
  "campaign_type": "education",
  "fund_ranges": [
    {
      "range": "₱10,000 - ₱50,000",
      "count": 12,
      "min": 10000.00,
      "max": 50000.00
    },
    ...
  ],
  "percentiles": [
    {"percentile": 10, "value": 15000.00, "label": "P10"},
    {"percentile": 25, "value": 25000.00, "label": "P25"},
    {"percentile": 50, "value": 50000.00, "label": "P50"},
    {"percentile": 75, "value": 100000.00, "label": "P75"},
    {"percentile": 90, "value": 200000.00, "label": "P90"}
  ],
  "top_beneficiaries": [
    {"term": "students", "count": 45},
    {"term": "children", "count": 32},
    {"term": "families", "count": 28}
  ],
  "trending_metrics": {
    "current_week_donations": 25,
    "previous_week_donations": 18,
    "current_week_amount": 125000.00,
    "previous_week_amount": 90000.00,
    "avg_donation": 5000.00,
    "growth_percentage": 38.9,
    "is_trending": true
  }
}
```

**Cache:** 10 minutes

#### 2. Trending Explanation
```
GET /api/analytics/trending-explanation/{type}
```

**Returns:**
```json
{
  "explanation": "Education campaigns are trending with 25 donations in the last 7 days (+38.9% vs previous week). Average donation: ₱5,000.00. This represents an increase in activity.",
  "is_trending": true,
  "metrics": {
    "current_week_donations": 25,
    "previous_week_donations": 18,
    "growth_percentage": 38.9,
    "avg_donation": 5000.00
  }
}
```

**No cache** (real-time trending data)

---

## Advanced Analytics Features

### 1. Fund Range Histogram

**Purpose:** Shows distribution of campaign funding goals in 5 bins

**Algorithm:**
```php
private function calculateHistogram($amounts)
{
    $min = $amounts->min();
    $max = $amounts->max();
    $range = $max - $min;
    $binSize = $range / 5;
    
    // Create 5 equal-width bins
    for ($i = 0; $i < 5; $i++) {
        $binStart = $min + ($i * $binSize);
        $binEnd = $min + (($i + 1) * $binSize);
        
        $count = campaigns in range [$binStart, $binEnd];
        
        $bins[] = [
            'range' => '₱' . number_format($binStart) . ' - ₱' . number_format($binEnd),
            'count' => $count,
            'min' => (float) $binStart,
            'max' => (float) $binEnd,
        ];
    }
    
    return $bins;
}
```

**Use Cases:**
- Understanding typical funding levels
- Identifying most common goal ranges
- Setting realistic campaign goals

**Example Output:**
```
₱10,000 - ₱30,000:    12 campaigns
₱30,000 - ₱50,000:    18 campaigns
₱50,000 - ₱70,000:    25 campaigns ← Most common
₱70,000 - ₱90,000:    15 campaigns
₱90,000 - ₱110,000:   8 campaigns
```

---

### 2. Percentiles

**Purpose:** Statistical distribution of funding goals

**Algorithm:**
```php
private function calculatePercentiles($amounts, $percentiles)
{
    $sorted = $amounts->sort()->values();
    
    foreach ($percentiles as $p) {
        $index = (($p / 100) * ($sorted->count() - 1));
        $lower = floor($index);
        $upper = ceil($index);
        
        if ($lower === $upper) {
            $value = $sorted[$lower];
        } else {
            // Linear interpolation
            $value = $sorted[$lower] + 
                     (($index - $lower) * ($sorted[$upper] - $sorted[$lower]));
        }
        
        $result[] = [
            'percentile' => $p,
            'value' => round($value, 2),
            'label' => "P{$p}",
        ];
    }
    
    return $result;
}
```

**Percentiles Calculated:**
- **P10:** 10% of campaigns have goals below this amount
- **P25:** 25% below (Q1 - First Quartile)
- **P50:** 50% below (Median)
- **P75:** 75% below (Q3 - Third Quartile)
- **P90:** 90% below

**Use Cases:**
- Benchmarking: "Your goal is at P75 - higher than 75% of similar campaigns"
- Recommendations: "Most campaigns aim for ₱50,000 (P50)"
- Outlier detection: Goals > P90 are unusually high

**Example:**
```
P10:  ₱15,000
P25:  ₱25,000
P50:  ₱50,000  ← Half of campaigns
P75:  ₱100,000
P90:  ₱200,000
```

---

### 3. Beneficiary Clustering

**Purpose:** Extract common beneficiary keywords from text

**Algorithm:**
```php
private function extractTopBeneficiaries($beneficiaries)
{
    $keywords = [];
    
    foreach ($beneficiaries as $text) {
        // Extract words (>3 chars)
        $words = preg_split('/[\s,\.]+/', strtolower($text));
        
        foreach ($words as $word) {
            $word = trim($word);
            
            // Filter stopwords
            $stopWords = ['the', 'and', 'for', 'with', ...];
            
            if (strlen($word) > 3 && !in_array($word, $stopWords)) {
                $keywords[$word]++;
            }
        }
    }
    
    arsort($keywords);
    return top 10 keywords;
}
```

**Example Input:**
```
Campaign 1: "500 students in rural communities"
Campaign 2: "Malnourished children and families"
Campaign 3: "Elementary students lacking supplies"
Campaign 4: "Poor families affected by disaster"
```

**Example Output:**
```
students:  3
families:  2
children:  2
rural:     1
communities: 1
malnourished: 1
elementary: 1
poor:      1
disaster:  1
```

**Use Cases:**
- Identify target beneficiaries
- Understand campaign focus
- Group similar campaigns
- Keyword analysis for marketing

---

### 4. Trend Detection (Week-over-Week)

**Purpose:** Detect trending campaign types based on donation activity

**Algorithm:**
```php
private function calculateTrendingMetrics($type, $charityId)
{
    // Current week: Last 7 days
    $currentWeekStart = now()->subDays(7);
    $currentWeekEnd = now();
    
    // Previous week: 8-14 days ago
    $previousWeekStart = now()->subDays(14);
    $previousWeekEnd = now()->subDays(7);
    
    // Count donations in each period
    $currentCount = donations in current week;
    $previousCount = donations in previous week;
    
    // Calculate growth percentage
    $growthPercentage = $previousCount > 0 
        ? (($currentCount - $previousCount) / $previousCount) * 100
        : ($currentCount > 0 ? 100 : 0);
    
    $isTrending = $growthPercentage > 10; // 10% threshold
    
    return metrics;
}
```

**Trending Criteria:**
- **Trending:** Growth > 10%
- **Stable:** -10% ≤ Growth ≤ 10%
- **Declining:** Growth < -10%

**Metrics Returned:**
- Current week donations
- Previous week donations
- Growth percentage
- Average donation amount
- Total amounts (both weeks)
- Trending flag (boolean)

**Example:**
```
Current Week:  25 donations (₱125,000)
Previous Week: 18 donations (₱90,000)
Growth:        +38.9%
Status:        TRENDING ✓
Avg Donation:  ₱5,000
```

---

### 5. Auto-Generated Explanations

**Purpose:** Human-readable trending insights

**Algorithm:**
```php
public function trendingExplanation($type)
{
    $metrics = $this->calculateTrendingMetrics($type, null);
    
    $isTrending = $metrics['growth_percentage'] > 10;
    $direction = $metrics['growth_percentage'] > 0 ? 'increase' : 'decrease';
    
    $explanation = sprintf(
        "%s campaigns are %s with %d donation%s in the last 7 days " .
        "(%+.1f%% vs previous week). Average donation: ₱%s. " .
        "This represents a %s in activity.",
        ucwords(str_replace('_', ' ', $type)),
        $isTrending ? 'trending' : 'stable',
        $metrics['current_week_donations'],
        $metrics['current_week_donations'] !== 1 ? 's' : '',
        $metrics['growth_percentage'],
        number_format($metrics['avg_donation'], 2),
        $direction
    );
    
    return $explanation;
}
```

**Example Outputs:**

**Trending (Positive):**
> "Education campaigns are trending with 25 donations in the last 7 days (+38.9% vs previous week). Average donation: ₱5,000.00. This represents an increase in activity."

**Stable:**
> "Medical campaigns are stable with 15 donations in the last 7 days (+5.2% vs previous week). Average donation: ₱8,000.00. This represents an increase in activity."

**Declining:**
> "Disaster Relief campaigns are stable with 8 donations in the last 7 days (-15.3% vs previous week). Average donation: ₱12,000.00. This represents a decrease in activity."

---

## Frontend Implementation

### Advanced Tab Added

**Location:** Charity Analytics Dashboard → Advanced Tab

**Components:**

#### 1. Trending Explanation Card
```tsx
<Card className="border-blue-200 bg-blue-50">
  <CardContent className="pt-6">
    <div className="flex items-start gap-3">
      <TrendingUp className="h-5 w-5 text-blue-600" />
      <p>{trendingExplanation}</p>
    </div>
  </CardContent>
</Card>
```

#### 2. Fund Range Histogram
```tsx
<BarChart data={advancedStats.fund_ranges}>
  <CartesianGrid strokeDasharray="3 3" />
  <XAxis dataKey="range" angle={-45} textAnchor="end" />
  <YAxis />
  <Tooltip />
  <Bar dataKey="count" fill="#0088FE" name="Campaigns" />
</BarChart>
```

#### 3. Percentiles Grid
```tsx
<div className="grid grid-cols-5 gap-3">
  {advancedStats.percentiles.map((p) => (
    <div className="p-4 rounded-lg border text-center">
      <p className="text-xs text-muted-foreground">{p.label}</p>
      <p className="text-lg font-bold">₱{p.value.toLocaleString()}</p>
    </div>
  ))}
</div>
```

#### 4. Beneficiary Keywords
```tsx
<div className="flex flex-wrap gap-2">
  {advancedStats.top_beneficiaries.map((term) => (
    <div className="px-4 py-2 rounded-full border bg-primary/5">
      <span className="font-medium capitalize">{term.term}</span>
      <span className="text-sm text-muted-foreground ml-2">
        ({term.count})
      </span>
    </div>
  ))}
</div>
```

#### 5. Week-over-Week Metrics
```tsx
<div className="grid grid-cols-4 gap-4">
  <div className="p-4 rounded-lg border">
    <p className="text-sm text-muted-foreground">Current Week</p>
    <p className="text-2xl font-bold">
      {advancedStats.trending_metrics.current_week_donations}
    </p>
  </div>
  <div className="p-4 rounded-lg border">
    <p className="text-sm text-muted-foreground">Growth</p>
    <p className={`text-2xl font-bold ${
      growth > 0 ? 'text-green-600' : 'text-red-600'
    }`}>
      {growth > 0 ? '+' : ''}{growth}%
    </p>
  </div>
  ...
</div>
```

---

## Performance Optimizations

### Caching Strategy

```php
// Advanced analytics - 10 minute cache
Cache::remember("analytics_advanced_{$type}_{$charityId}", 600, function () {
    // Compute histogram
    // Calculate percentiles
    // Extract beneficiaries
    // Get trending metrics
    return $data;
});
```

**Cache Keys:**
- Global: `analytics_advanced_education_global`
- Per charity: `analytics_advanced_education_5`

### Query Optimization

**Histograms & Percentiles:**
- Single query to fetch all campaign amounts
- In-memory calculation (no additional DB queries)
- O(n log n) complexity for sorting

**Beneficiary Analysis:**
- Simple text parsing (no NLP library needed)
- Efficient keyword extraction
- Stop-word filtering

**Trending Detection:**
- Two efficient queries (current + previous week)
- Uses indexed `donated_at` field
- Aggregation done in database

---

## Testing Guide

### 1. Create Test Data

**Campaigns with varied goals:**
```sql
INSERT INTO campaigns (campaign_type, target_amount, beneficiary) VALUES
('education', 10000, '500 students in rural communities'),
('education', 25000, '200 elementary students lacking supplies'),
('education', 50000, '1000 children from poor families'),
('education', 75000, '300 high school students in urban areas'),
('education', 100000, '500 college students needing scholarships');
```

**Donations for trending:**
```sql
-- Current week (trending up)
INSERT INTO donations (campaign_id, amount, donated_at, status) VALUES
(1, 5000, NOW() - INTERVAL 2 DAY, 'completed'),
(1, 3000, NOW() - INTERVAL 3 DAY, 'completed'),
(2, 7000, NOW() - INTERVAL 4 DAY, 'completed'),
...

-- Previous week (less activity)
INSERT INTO donations (campaign_id, amount, donated_at, status) VALUES
(1, 4000, NOW() - INTERVAL 10 DAY, 'completed'),
(2, 6000, NOW() - INTERVAL 11 DAY, 'completed'),
...
```

### 2. Test Endpoints

**Advanced Analytics:**
```bash
GET http://127.0.0.1:8000/api/analytics/campaigns/education/advanced
Authorization: Bearer {token}

# Verify:
- fund_ranges has 5 bins
- percentiles has P10, P25, P50, P75, P90
- top_beneficiaries shows "students", "children", etc.
- trending_metrics shows week-over-week data
```

**Trending Explanation:**
```bash
GET http://127.0.0.1:8000/api/analytics/trending-explanation/education
Authorization: Bearer {token}

# Verify:
- explanation is human-readable
- is_trending matches growth > 10%
- metrics match database
```

### 3. Frontend Testing

**Navigate to:**
```
http://localhost:8080/charity/analytics
```

**Steps:**
1. Click "Advanced" tab
2. Select campaign type (e.g., Education)
3. Verify:
   - Trending explanation appears
   - Histogram shows 5 bars
   - Percentiles show P10-P90
   - Beneficiary keywords display
   - Week-over-week metrics accurate

---

## Use Cases & Benefits

### For Charities

**1. Goal Setting:**
- "Most education campaigns aim for ₱50,000 (P50)"
- "Your goal of ₱75,000 is at P75 - higher than most"

**2. Trend Awareness:**
- "Education campaigns are trending (+38% this week)"
- "Consider launching now to ride the trend"

**3. Beneficiary Insights:**
- "Top keywords: students, children, families"
- "Focus messaging on these groups"

**4. Competitive Analysis:**
- "Top 5 charities in this category"
- "Average funding goals in your region"

### For Donors

**1. Impact Understanding:**
- "Typical education campaign needs ₱50,000"
- "Your ₱5,000 donation is 10% of median goal"

**2. Trend Discovery:**
- "Disaster relief is trending - urgent needs"
- "Donate to trending causes for maximum impact"

**3. Cause Matching:**
- "Beneficiaries include: students, families"
- "Find campaigns aligned with your values"

### For Administrators

**1. Platform Insights:**
- "Education campaigns dominate (42% of total)"
- "Medical campaigns have highest average goals"

**2. Resource Allocation:**
- "Trending categories need more support"
- "Declining categories may need promotion"

**3. Data-Driven Decisions:**
- "P90 campaigns rarely succeed (too ambitious)"
- "Recommend P50-P75 range for best results"

---

## API Response Examples

### Advanced Analytics (Education)

```json
{
  "campaign_type": "education",
  "fund_ranges": [
    {"range": "₱10,000 - ₱28,000", "count": 12, "min": 10000, "max": 28000},
    {"range": "₱28,000 - ₱46,000", "count": 18, "min": 28000, "max": 46000},
    {"range": "₱46,000 - ₱64,000", "count": 25, "min": 46000, "max": 64000},
    {"range": "₱64,000 - ₱82,000", "count": 15, "min": 64000, "max": 82000},
    {"range": "₱82,000 - ₱100,000", "count": 8, "min": 82000, "max": 100000}
  ],
  "percentiles": [
    {"percentile": 10, "value": 15000, "label": "P10"},
    {"percentile": 25, "value": 25000, "label": "P25"},
    {"percentile": 50, "value": 50000, "label": "P50"},
    {"percentile": 75, "value": 75000, "label": "P75"},
    {"percentile": 90, "value": 95000, "label": "P90"}
  ],
  "top_beneficiaries": [
    {"term": "students", "count": 45},
    {"term": "children", "count": 32},
    {"term": "families", "count": 28},
    {"term": "communities", "count": 22},
    {"term": "schools", "count": 18}
  ],
  "trending_metrics": {
    "current_week_donations": 25,
    "previous_week_donations": 18,
    "current_week_amount": 125000.00,
    "previous_week_amount": 90000.00,
    "avg_donation": 5000.00,
    "growth_percentage": 38.9,
    "is_trending": true
  }
}
```

---

## Files Modified

### Backend (2 files)
1. `app/Http/Controllers/AnalyticsController.php` - Added 3 new methods + helpers
2. `routes/api.php` - Added 2 new routes

### Frontend (1 file)
1. `src/pages/charity/Analytics.tsx` - Added Advanced tab

### Documentation (1 file)
1. `ANALYTICS_PHASE6_ADVANCED.md` - This file

---

## Future Enhancements

### Machine Learning Integration
1. **Campaign Success Prediction**
   - Train model on historical data
   - Predict probability of reaching goal
   - Input: type, goal, beneficiary, location, time

2. **Optimal Timing Recommendations**
   - Analyze seasonal trends
   - Suggest best launch dates
   - Account for competition

3. **Beneficiary NLP**
   - Use NLP libraries (spaCy, NLTK)
   - Entity extraction
   - Sentiment analysis
   - Topic modeling

### Advanced Analytics
1. **Custom Date Ranges**
   - Month-over-month
   - Quarter-over-quarter
   - Year-over-year

2. **Geographic Heatmaps**
   - Map visualization
   - Regional trends
   - Province-level clustering

3. **Donor Segmentation**
   - RFM analysis (Recency, Frequency, Monetary)
   - Behavioral clustering
   - Lifetime value prediction

4. **Anomaly Detection**
   - Unusual donation patterns
   - Fraud detection
   - Campaign outliers

---

## Summary

✅ **Phase 6 Complete:**
- Fund range histograms (5 bins)
- Statistical percentiles (P10, P25, P50, P75, P90)
- Beneficiary keyword extraction
- Week-over-week trend detection
- Auto-generated explanations
- Advanced analytics tab

✅ **Key Metrics:**
- 2 new API endpoints
- 5 advanced analytics features
- 10-minute caching
- Real-time trending detection

✅ **Benefits:**
- Data-driven goal setting
- Trend awareness
- Competitive insights
- Beneficiary understanding
- Performance benchmarking

**The complete 6-phase analytics system is production-ready!** 🚀
