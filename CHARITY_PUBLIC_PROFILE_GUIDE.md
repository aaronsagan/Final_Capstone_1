# Charity Public Profile Page - Implementation Guide

## Overview
A comprehensive, public-facing page where donors and users can view a charity's full profile, including updates (threads), campaigns, and transparency information.

## Features Implemented

### 1. **Header / Hero Section**
- ✅ Banner image / cover photo with gradient overlay
- ✅ Large charity logo (rounded with ring effect)
- ✅ Charity name (large and prominent)
- ✅ Verification badge (if verified)
- ✅ Short tagline / mission statement
- ✅ Follow/Unfollow button (requires login)
- ✅ Share button (native share API + clipboard fallback)
- ✅ Quick stats cards:
  - Total Raised
  - Active Campaigns
  - Followers Count
  - Total Updates

### 2. **Sticky Tabs Navigation**
Three main tabs for content switching:

#### **Updates Tab (Threads)**
- Displays all posts/threads created by the charity
- Each post shows:
  - Charity logo and name
  - Verification badge
  - Pinned badge (if pinned)
  - Post images/media (1-4 images in grid)
  - Post content
  - Timestamp (relative time)
  - Like count and comment count
  - Like and Comment action buttons
- Threaded replies display with visual connectors
- Comment system:
  - View existing comments
  - Add new comments (login required)
  - Scroll area for long comment lists
- Empty state with friendly message

#### **Campaigns Tab**
- Grid layout of campaign cards (2 columns on desktop)
- Uses existing `CampaignCard` component
- Shows all campaigns (active, completed, expired)
- Each card displays:
  - Banner image
  - Status badge
  - Title and description
  - Progress bar
  - Amount raised vs goal
  - Donor count
  - Days left
  - View Details button
- Empty state if no campaigns

#### **About Tab**
- **Mission** - Full mission statement
- **Vision** - Vision statement (if provided)
- **Services** - Services offered (if provided)
- **Transparency Info**:
  - Registration number
  - Join date on platform

### 3. **Sidebar (Desktop)**
Sticky contact information panel:
- **Email** - Clickable mailto link
- **Phone** - Clickable tel link
- **Address** - Full address display
- **Social Media Links**:
  - Website
  - Facebook
  - Twitter/X
  - Instagram

### 4. **Empty States**
Friendly placeholders when:
- No updates posted yet
- No campaigns created yet
- Encourages users to follow the charity

## Technical Implementation

### Routes
```typescript
// Public route - no authentication required
/charity/profile/:id
```

### API Endpoints Used
```typescript
// Charity profile
GET /api/charities/{id}

// Charity updates (threads)
GET /api/charities/{id}/updates

// Charity campaigns
GET /api/charities/{id}/campaigns

// Charity statistics
GET /api/charities/{id}/stats

// Follow/Unfollow (requires auth)
POST /api/charities/{id}/follow

// Check follow status
GET /api/charities/{id}/follow-status

// Like update
POST /api/updates/{id}/like

// Comment operations
GET /api/updates/{id}/comments
POST /api/updates/{id}/comments
```

### Services Extended
**`charity.ts`**:
- `getPublicCharityProfile(charityId)`
- `getCharityCampaigns(charityId, params?)`
- `toggleFollow(charityId)`
- `checkFollowStatus(charityId)`
- `getCharityStats(charityId)`

**`updates.ts`** (already existed):
- `getCharityUpdates(charityId)`
- `toggleLike(updateId)`
- `getComments(updateId)`
- `addComment(updateId, content)`

## Design Features

### Responsive Layout
- Mobile: Single column, stacked layout
- Desktop: Two-column with sticky sidebar
- Tablet: Optimized spacing and grid adjustments

### Dark Mode Support
- All components adapt to light/dark themes
- Proper contrast and readability in both modes

### Modern UI Elements
- Smooth transitions and hover effects
- Gradient overlays and backgrounds
- Card-based content layout
- Icon indicators for all actions
- Badge system for status indicators

### Visual Consistency
- Matches existing dashboard design language
- Reuses UI components (Card, Button, Avatar, etc.)
- Consistent spacing and typography
- Professional color scheme with semantic colors

## How to Use

### For Developers

1. **Navigate to charity profile**:
```typescript
// From anywhere in the app
navigate(`/charity/profile/${charityId}`);

// Or as a link
<Link to={`/charity/profile/${charityId}`}>View Profile</Link>
```

2. **Update existing charity links**:
Replace charity name/logo click handlers to navigate to this page:
```typescript
onClick={() => navigate(`/charity/profile/${charity.id}`)}
```

3. **Backend requirements**:
Ensure your backend implements the following endpoints:
- `GET /api/charities/{id}` - Returns charity profile
- `GET /api/charities/{id}/updates` - Returns charity updates
- `GET /api/charities/{id}/campaigns` - Returns campaigns
- `GET /api/charities/{id}/stats` - Returns stats (followers_count, etc.)
- `POST /api/charities/{id}/follow` - Toggle follow (auth required)
- `GET /api/charities/{id}/follow-status` - Check if user follows

### For Users

1. **Access the page**:
   - Click any charity name or logo across the platform
   - Direct URL: `/charity/profile/{id}`

2. **Interact with content**:
   - View all updates and campaigns
   - Like and comment on posts (login required)
   - Follow/unfollow the charity (login required)
   - Share the charity profile
   - Contact via email, phone, or social media

3. **Learn about the charity**:
   - Read mission, vision, and services
   - View transparency information
   - See campaign impact statistics

## Future Enhancements (Optional)

- [ ] Google Maps integration for location
- [ ] Real-time updates via WebSockets
- [ ] Filter campaigns by status
- [ ] Search within charity updates
- [ ] Download impact reports
- [ ] Donation analytics graphs
- [ ] Volunteer sign-up integration
- [ ] Newsletter subscription
- [ ] Charity reviews/testimonials section

## Browser Compatibility
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Responsive design works on all screen sizes
- Native share API with clipboard fallback

## Performance Considerations
- Lazy loading for images
- Pagination ready (backend support needed)
- Optimized re-renders with proper state management
- Efficient comment loading (only when expanded)

---

**Status**: ✅ Fully Implemented and Ready for Testing

**Last Updated**: October 15, 2025
