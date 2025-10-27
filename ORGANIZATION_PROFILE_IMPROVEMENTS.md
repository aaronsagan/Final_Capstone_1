# Organization Profile Management - Implementation Plan

## 🎯 Overview
Complete redesign of the Organization Profile page with modern UI, comprehensive features, and professional design.

## ✅ Key Features to Implement

### 1. **Hero Section with Banner & Logo**
- Large banner image (1200x400px recommended)
- Circular logo overlay on banner
- Camera icon buttons for easy upload
- Preview before save
- Verified badge display

### 2. **Tabbed Interface**
Five main tabs:
- **Profile**: Basic info, mission/vision, focus areas, contact
- **Team**: Team members with roles and bios
- **Media**: Gallery of images/videos
- **Campaigns**: Quick access to campaign management
- **Settings**: Account settings and danger zone

### 3. **Profile Tab Sections**
- Basic Information (name, tagline, description)
- Mission, Vision & Core Values
- History & Background
- Focus Areas (multi-select badges)
- Contact Information (address, email, phone, website)
- Social Media Links (Facebook, Twitter, Instagram, LinkedIn)

### 4. **Team Management**
- Add/Edit/Delete team members
- Fields: Name, Role, Bio, Profile Image
- Public/Private visibility toggle
- Grid layout with avatar cards
- Modal form for adding members

### 5. **Media Gallery**
- Drag-and-drop upload
- Image and video support
- Masonry grid layout
- Lightbox preview
- Category tags (Events, Volunteering, Campaigns)
- Delete functionality

### 6. **Campaigns Quick Access**
- List of active and past campaigns
- Campaign cards with:
  - Title
  - Total donations
  - Status badge
  - "Manage" button
- "Create New Campaign" button

### 7. **Account Settings**
- Email notifications toggles
- Donation alerts
- Campaign updates
- Comment notifications
- Profile visibility (Public/Private)
- Danger Zone:
  - Deactivate account
  - Delete account (with confirmation)

### 8. **UX Enhancements**
- Sticky header with "Save Changes" and "Preview Public Profile" buttons
- "Unsaved changes" indicator
- Auto-save or navigation warning
- Toast notifications for all actions
- Loading states
- Form validation
- Character counters
- Dark/light mode support
- Fully responsive design

## 🎨 Design Specifications

### Colors
- Primary: Gold (#F59E0B)
- Secondary: Navy (#1E40AF)
- Success: Green (#10B981)
- Destructive: Red (#EF4444)

### Typography
- Headers: Bold, 2xl-4xl
- Body: Regular, sm-base
- Muted text: text-muted-foreground

### Components
- Rounded corners (rounded-lg)
- Subtle shadows (shadow-lg)
- Smooth transitions (transition-all)
- Hover effects on interactive elements
- Icon headers for sections

### Layout
- Max width: 7xl container
- Padding: px-4 py-6
- Card-based sections
- Grid layouts for team/media
- Sticky header

## 📋 Implementation Checklist

### Phase 1: Structure & Layout
- [ ] Create tabbed interface
- [ ] Add banner/logo upload section
- [ ] Implement sticky header
- [ ] Add unsaved changes indicator

### Phase 2: Profile Tab
- [ ] Basic information form
- [ ] Mission/Vision/Values sections
- [ ] History section
- [ ] Focus areas multi-select
- [ ] Contact information form
- [ ] Social media links

### Phase 3: Team Management
- [ ] Team member list/grid
- [ ] Add member modal
- [ ] Edit member functionality
- [ ] Delete member with confirmation
- [ ] Public/private toggle

### Phase 4: Media Gallery
- [ ] File upload (drag-and-drop)
- [ ] Grid layout with masonry
- [ ] Lightbox preview
- [ ] Delete functionality
- [ ] Category filtering

### Phase 5: Campaigns Integration
- [ ] Fetch campaigns from API
- [ ] Display campaign cards
- [ ] Link to campaign management
- [ ] Create new campaign button

### Phase 6: Settings & Security
- [ ] Notification preferences
- [ ] Visibility settings
- [ ] Change password
- [ ] Deactivate account
- [ ] Delete account with confirmation

### Phase 7: Polish & Testing
- [ ] Form validation
- [ ] Error handling
- [ ] Loading states
- [ ] Toast notifications
- [ ] Responsive design testing
- [ ] Dark mode testing
- [ ] API integration
- [ ] Performance optimization

## 🔌 API Endpoints Needed

```typescript
// Profile
PUT /api/charities/{id} - Update charity profile
POST /api/charities/{id}/logo - Upload logo
POST /api/charities/{id}/banner - Upload banner

// Team
GET /api/charities/{id}/team - Get team members
POST /api/charities/{id}/team - Add team member
PUT /api/charities/{id}/team/{memberId} - Update member
DELETE /api/charities/{id}/team/{memberId} - Delete member

// Media
GET /api/charities/{id}/media - Get media items
POST /api/charities/{id}/media - Upload media
DELETE /api/charities/{id}/media/{mediaId} - Delete media

// Campaigns
GET /api/charities/{id}/campaigns - Get campaigns

// Settings
PUT /api/charities/{id}/settings - Update settings
POST /api/charities/{id}/deactivate - Deactivate account
DELETE /api/charities/{id} - Delete account
```

## 🚀 Quick Start

The existing `OrganizationProfile.tsx` file needs to be completely refactored with:
1. New tabbed interface
2. Banner/logo upload section
3. Expanded form fields
4. Team management
5. Media gallery
6. Settings panel

Due to file size, this should be split into multiple components:
- `OrganizationProfileHeader.tsx` - Banner & logo
- `OrganizationProfileForm.tsx` - Main profile form
- `TeamManagement.tsx` - Team member management
- `MediaGallery.tsx` - Media upload & display
- `OrganizationSettings.tsx` - Settings panel

## 📝 Notes

- All changes should be saved to backend
- Use FormData for file uploads
- Implement proper error handling
- Add loading states for all async operations
- Use toast notifications for user feedback
- Maintain consistent styling with donor dashboard
- Ensure mobile responsiveness
- Support dark/light themes
