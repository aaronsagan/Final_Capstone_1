# Charity Card Component - Feature Reference

## 🎨 Visual Layout

```
┌─────────────────────────────────────────┐
│  [Featured Badge]    [Verified Badge]   │  ← Top badges
│                                          │
│         CHARITY LOGO/IMAGE               │  ← Clickable with zoom hover
│         (hover: zoom + overlay)          │
│                                          │
└─────────────────────────────────────────┘
┌─────────────────────────────────────────┐
│  Charity Name (clickable)                │  ← Bold, truncates
│  Short mission description...            │  ← 2 lines max
│                                          │
│  📍 City, Region                         │  ← Location
│  [Category Badge]                        │  ← Category tag
│                                          │
│  ┌──────┬──────────┬──────────┐         │
│  │ 👥   │   🎯     │   📈     │         │  ← Stats grid
│  │ 2.4K │   12     │  ₱500K   │         │
│  │Follw │Campaign  │ Raised   │         │
│  └──────┴──────────┴──────────┘         │
│                                          │
│  [💝 Donate] [➕] [👁️]                  │  ← Action buttons
│                                          │
│  Supported by 2.4K donors                │  ← Social proof
└─────────────────────────────────────────┘
```

---

## 🖱️ Interactive Elements

### Hover Effects:

#### 1. **Image Hover**
```
Normal State:
- Image at scale(1.0)
- Overlay opacity: 0.6

Hover State:
- Image scales to 1.1 (zoom in)
- Overlay opacity: 1.0 (darker)
- "View Profile" text appears
- Eye icon (👁️) shows in center
- Transition: 500ms smooth
```

#### 2. **Card Hover**
```
- Shadow increases (shadow-lg → shadow-2xl)
- Subtle lift effect
- Transition: 300ms
```

#### 3. **Button Hover**
```
Donate Button:
- Glow effect (shadow-xl)
- Primary color intensifies
- Gradient shifts

Follow Button:
- Border color changes to primary
- Background tint on outline variant

View Button:
- Border and text turn primary
- Background tint
```

---

## 🎯 Clickable Areas

### Primary Click Targets:

1. **Charity Image** → `/donor/charities/{id}`
2. **Charity Name** → `/donor/charities/{id}`
3. **Card Background** → `/donor/charities/{id}`
4. **Donate Button** → `/donor/donate/{id}`
5. **Follow Button** → Toggle follow state
6. **View Button** → `/donor/charities/{id}`

### Click Behavior:
- All buttons use `e.stopPropagation()` to prevent card click
- Card click navigates to profile
- Smooth navigation with React Router

---

## 📊 Data Display

### Information Hierarchy:

#### **Tier 1: Primary Info**
- Charity Name (20px, bold)
- Logo/Image (224px height)

#### **Tier 2: Supporting Info**
- Mission/Description (14px, 2 lines)
- Location (14px with icon)
- Category badge

#### **Tier 3: Stats**
- Followers count
- Campaigns count
- Total raised amount

#### **Tier 4: Actions**
- Donate (primary CTA)
- Follow (secondary action)
- View (tertiary action)

---

## 🎨 Color Coding

### Status Colors:
```
✅ Verified Badge:     bg-green-600
⭐ Featured Badge:     gradient amber-500 → orange-500
📍 Location:           text-muted-foreground
🏷️ Category:          border-outline

Stats Colors:
👥 Followers:          text-primary (gold/orange)
🎯 Campaigns:          text-blue-600
📈 Total Raised:       text-green-600
```

### Button Colors:
```
Donate:    gradient primary → primary/80
           hover: shadow-xl with primary/20 glow

Follow:    
  - Not following: outline with hover primary tint
  - Following: solid primary background

View:      outline with hover primary border
```

---

## 📱 Responsive Behavior

### Breakpoints:

#### **Mobile (< 768px)**
```
- Grid: 1 column
- Card width: 100%
- Stats: 3 columns (compact)
- Buttons: Full width stack
- Image height: 224px
```

#### **Tablet (768px - 1024px)**
```
- Grid: 2 columns
- Card width: ~48%
- Stats: 3 columns
- Buttons: Horizontal row
- Image height: 224px
```

#### **Desktop (> 1024px)**
```
- Grid: 3 columns
- Card width: ~32%
- Stats: 3 columns
- Buttons: Horizontal row
- Image height: 224px
```

---

## 🔢 Number Formatting

### Smart Formatting Function:
```typescript
formatNumber(num):
  >= 1,000,000  → "2.5M"
  >= 1,000      → "1.2K"
  < 1,000       → "500"

Examples:
  2,450,000 → "2.5M"
  12,500    → "12.5K"
  850       → "850"
```

### Currency Display:
```
₱500,000 → ₱500K (in card)
₱500,000 → ₱500,000 (in tooltip)
```

---

## 💬 Tooltips

### Tooltip Locations:

1. **Verification Badge**
   - Trigger: Hover over green checkmark
   - Content: "This charity has been verified by CharityHub"

2. **Followers Stat**
   - Trigger: Hover over follower count
   - Content: "2,450 followers" (full number)

3. **Campaigns Stat**
   - Trigger: Hover over campaign count
   - Content: "12 active campaigns"

4. **Total Raised Stat**
   - Trigger: Hover over raised amount
   - Content: "₱500,000 total raised" (full amount)

---

## 🎭 Conditional Display

### Featured Badge:
```typescript
Shows when:
  - verification_status === 'approved'
  - total_raised > 100,000

Badge:
  - Gold gradient (amber → orange)
  - Star icon (filled)
  - Text: "Featured"
  - Position: Top-left corner
```

### Social Proof Text:
```typescript
Shows when:
  - followers_count > 100

Text:
  - "Supported by {count} donors"
  - Position: Bottom of card
  - Style: Small, muted, centered
```

### Verification Badge:
```typescript
Shows when:
  - verification_status === 'approved'

Badge:
  - Green background
  - Checkmark icon
  - Text: "Verified"
  - Position: Top-right corner
  - Has tooltip
```

---

## 🔄 State Management

### Component States:

#### **Local State:**
```typescript
stats: {
  followers_count: number
  campaigns_count: number
  total_raised: number
}

isHovered: boolean
following: boolean
isLoading: boolean
```

#### **Props:**
```typescript
charity: Charity object
isFollowing: boolean (from parent)
onFollowToggle: callback function
```

#### **State Flow:**
```
1. Component mounts
2. Fetch stats from API (3 endpoints)
3. Update stats state
4. Render with data

On Follow Click:
1. Set isLoading = true
2. Call API
3. Update following state
4. Update followers_count (+1 or -1)
5. Call onFollowToggle callback
6. Set isLoading = false
7. Show toast notification
```

---

## 🚀 Performance Features

### Optimizations:

1. **Lazy Loading Images**
   ```html
   <img loading="lazy" ... />
   ```

2. **Efficient Re-renders**
   - useEffect with proper dependencies
   - State updates batched

3. **API Call Management**
   - Fetch stats only on mount
   - No unnecessary refetches
   - Error handling prevents crashes

4. **CSS Transitions**
   - Hardware-accelerated (transform, opacity)
   - Smooth 60fps animations

---

## 🎬 Animation Timeline

### Hover Animation Sequence:
```
0ms:    User hovers over card
0-300ms: Card shadow expands
0-500ms: Image scales from 1.0 to 1.1
0-300ms: Overlay fades from 0.6 to 1.0
0-300ms: "View Profile" text fades in

On Hover Out:
0-300ms: All effects reverse
```

### Follow Button Animation:
```
0ms:    User clicks follow
0ms:    Button shows loading state
~500ms: API responds
0ms:    Button updates icon
0-300ms: Follower count animates
0ms:    Toast notification appears
```

---

## 🧪 Testing Scenarios

### Manual Test Cases:

#### **Visual Tests:**
- [ ] Image loads correctly
- [ ] Hover zoom works smoothly
- [ ] Overlay appears on hover
- [ ] All text is readable
- [ ] Icons display properly
- [ ] Badges show when appropriate

#### **Interaction Tests:**
- [ ] Click image → navigates
- [ ] Click name → navigates
- [ ] Click card → navigates
- [ ] Click donate → navigates to donate page
- [ ] Click follow → toggles state
- [ ] Click view → navigates
- [ ] Tooltips appear on hover

#### **Data Tests:**
- [ ] Follower count displays
- [ ] Campaign count displays
- [ ] Total raised displays
- [ ] Numbers format correctly (K/M)
- [ ] Location shows correctly
- [ ] Category badge appears

#### **Responsive Tests:**
- [ ] Mobile: 1 column layout
- [ ] Tablet: 2 column layout
- [ ] Desktop: 3 column layout
- [ ] Text truncates properly
- [ ] Buttons remain accessible

#### **Edge Cases:**
- [ ] No image: shows placeholder
- [ ] Zero followers: shows "0"
- [ ] Very long name: truncates
- [ ] No category: hides badge
- [ ] Not verified: no badge
- [ ] Not logged in: follow shows error

---

## 📋 Component Props API

### CharityCardProps Interface:

```typescript
interface CharityCardProps {
  charity: {
    id: number;              // Required
    name: string;            // Required
    mission?: string;        // Optional
    category?: string;       // Optional
    region?: string;         // Optional
    municipality?: string;   // Optional
    logo_path?: string;      // Optional
    verification_status: string; // Required
  };
  isFollowing?: boolean;     // Optional, default: false
  onFollowToggle?: (charityId: number) => void; // Optional
}
```

### Usage Example:

```tsx
<CharityCard
  charity={{
    id: 1,
    name: "Hope Foundation",
    mission: "Helping communities thrive",
    category: "Community Development",
    region: "Metro Manila",
    municipality: "Quezon City",
    logo_path: "logos/hope.jpg",
    verification_status: "approved"
  }}
  isFollowing={true}
  onFollowToggle={(id) => console.log(`Toggled charity ${id}`)}
/>
```

---

## 🔗 Related Files

### Component Dependencies:
- `@/components/ui/card` - Card container
- `@/components/ui/button` - Action buttons
- `@/components/ui/badge` - Status badges
- `@/components/ui/tooltip` - Hover tooltips
- `@/services/auth` - Authentication service
- `lucide-react` - Icon library
- `react-router-dom` - Navigation
- `sonner` - Toast notifications

### Parent Components:
- `pages/donor/BrowseCharities.tsx` - Main usage

### API Endpoints:
- `GET /api/charities/{id}/followers-count`
- `GET /api/charities/{id}/campaigns`
- `GET /api/charities/{id}`
- `POST /api/charities/{id}/follow`

---

**Component Version**: 1.0.0  
**Last Updated**: 2025-01-16  
**Maintained By**: CharityHub Development Team
