# Bin/Trash System - Complete Implementation

## Overview
Fully functional bin/trash system for managing deleted posts with automatic 30-day cleanup, restore functionality, and permanent delete options.

## Features Implemented

### 1. Soft Delete System
- Posts are soft-deleted (not permanently removed)
- `deleted_at` timestamp tracks when post was deleted
- Deleted posts hidden from main feed
- Can be restored within 30 days

### 2. Bin Management Page
**Location**: `/charity/bin`

**Features**:
- View all trashed posts
- Shows days remaining until permanent deletion
- Warning badge for posts expiring soon (≤7 days)
- Restore posts
- Permanently delete posts
- Empty state when no trashed posts

### 3. Automatic Cleanup
- Scheduled task runs daily
- Automatically deletes posts older than 30 days
- Removes associated media files
- Logs cleanup activity

## Backend Implementation

### Database
**Migration**: `2025_10_20_090000_add_soft_deletes_to_updates.php`
- Adds `deleted_at` column to `updates` table

### Model
**File**: `app/Models/Update.php`
- Uses `SoftDeletes` trait
- `delete()` now soft deletes
- `forceDelete()` permanently deletes

### Controller Methods
**File**: `app/Http/Controllers/UpdateController.php`

1. **`destroy($id)`** - Soft delete (move to bin)
   ```php
   DELETE /api/updates/{id}
   ```

2. **`getTrashed()`** - Get all trashed posts
   ```php
   GET /api/updates/trash
   ```

3. **`restore($id)`** - Restore from bin
   ```php
   POST /api/updates/{id}/restore
   ```

4. **`forceDelete($id)`** - Permanently delete
   ```php
   DELETE /api/updates/{id}/force
   ```

### Scheduled Task
**File**: `app/Console/Commands/CleanupTrashedUpdates.php`

**Command**: `php artisan cleanup:trashed-updates`

**Schedule**: Daily (configured in `routes/console.php`)

**What it does**:
- Finds posts deleted more than 30 days ago
- Deletes associated media files
- Permanently removes posts from database
- Logs cleanup results

## Frontend Implementation

### Bin Page Component
**File**: `src/pages/charity/Bin.tsx`

**Features**:
- Lists all trashed posts
- Shows countdown timer (days remaining)
- Color-coded badges:
  - Red: ≤7 days remaining (expiring soon)
  - Gray: >7 days remaining
- Action buttons:
  - **Restore**: Brings post back to feed
  - **Delete Forever**: Permanently removes post
- Warning alerts for posts expiring soon
- Empty state with helpful message

**UI Elements**:
- Post content preview
- Media thumbnails (dimmed)
- Like/comment counts
- Deletion date
- Days remaining badge

### Delete Dialog
**File**: `src/components/ui/delete-dialog.tsx`

Custom dialog matching Facebook's design:
- Title: "Move to your bin?"
- Description: "Items in your bin will be automatically deleted after 30 days..."
- Buttons: "Cancel" and "Move"

### Navigation
**Updated**: `src/components/charity/CharitySidebar.tsx`
- Added "Bin" menu item with trash icon
- Located between "Fund Tracking" and "Profile"

### Routing
**Updated**: `src/App.tsx`
- Added route: `/charity/bin` → `<Bin />`

## User Flow

### Deleting a Post
1. User clicks "Delete" on a post
2. Custom dialog appears: "Move to your bin?"
3. User clicks "Move"
4. Post soft-deleted (moved to bin)
5. Toast: "Post moved to bin. It will be permanently deleted after 30 days."
6. Post disappears from feed

### Viewing Bin
1. User navigates to "Bin" in sidebar
2. Sees list of all deleted posts
3. Each post shows:
   - Days remaining until permanent deletion
   - Deletion date
   - Post content and media
   - Action buttons (Restore / Delete Forever)

### Restoring a Post
1. User clicks "Restore" on a trashed post
2. Confirmation dialog appears
3. User confirms
4. Post restored to feed
5. Toast: "Post restored successfully"
6. Post removed from bin

### Permanently Deleting
1. User clicks "Delete Forever"
2. Warning dialog appears: "This action cannot be undone"
3. User confirms
4. Post and media permanently deleted
5. Toast: "Post permanently deleted"
6. Post removed from bin

### Automatic Cleanup (After 30 Days)
1. Scheduled task runs daily at midnight
2. Finds posts with `deleted_at` ≥ 30 days ago
3. Deletes media files from storage
4. Permanently removes posts from database
5. Logs cleanup activity

## API Endpoints

### Soft Delete Post
```http
DELETE /api/updates/{id}
Authorization: Bearer {token}

Response 200:
{
  "message": "Post moved to bin. It will be permanently deleted after 30 days.",
  "deleted_at": "2025-10-20T08:30:00.000000Z"
}
```

### Get Trashed Posts
```http
GET /api/updates/trash
Authorization: Bearer {token}

Response 200:
{
  "data": [
    {
      "id": 1,
      "charity_id": 1,
      "content": "Post content...",
      "media_urls": ["path/to/image.jpg"],
      "created_at": "2025-10-01T10:00:00.000000Z",
      "deleted_at": "2025-10-20T08:30:00.000000Z",
      "likes_count": 5,
      "comments_count": 3
    }
  ]
}
```

### Restore Post
```http
POST /api/updates/{id}/restore
Authorization: Bearer {token}

Response 200:
{
  "message": "Post restored successfully",
  "update": { ... }
}
```

### Permanently Delete
```http
DELETE /api/updates/{id}/force
Authorization: Bearer {token}

Response 200:
{
  "message": "Post permanently deleted"
}
```

## Running the Scheduled Task

### Manually
```bash
php artisan cleanup:trashed-updates
```

### Automatically (Production)
Add to your server's cron:
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

This runs Laravel's scheduler every minute, which will execute the cleanup task daily.

## Visual Design

### Bin Page Layout
```
┌─────────────────────────────────────────────┐
│ 🗑️ Bin                                      │
│ Posts in your bin will be automatically     │
│ deleted after 30 days.                      │
├─────────────────────────────────────────────┤
│                                             │
│ ┌─────────────────────────────────────────┐ │
│ │ [23 days remaining] [Pinned]            │ │
│ │ 📅 Deleted on Oct 20, 2025, 4:30 PM     │ │
│ │                    [Restore] [Delete ∞] │ │
│ ├─────────────────────────────────────────┤ │
│ │ Post content goes here...               │ │
│ │                                         │ │
│ │ [Image] [Image]                         │ │
│ │                                         │ │
│ │ 5 likes  3 comments                     │ │
│ └─────────────────────────────────────────┘ │
│                                             │
│ ┌─────────────────────────────────────────┐ │
│ │ [5 days remaining] ⚠️                   │ │
│ │ 📅 Deleted on Oct 15, 2025, 2:15 PM     │ │
│ │                    [Restore] [Delete ∞] │ │
│ ├─────────────────────────────────────────┤ │
│ │ Another post content...                 │ │
│ │                                         │ │
│ │ ⚠️ This post will be permanently        │ │
│ │    deleted in 5 days. Restore it now    │ │
│ │    if you want to keep it.              │ │
│ └─────────────────────────────────────────┘ │
└─────────────────────────────────────────────┘
```

### Empty State
```
┌─────────────────────────────────────────────┐
│                                             │
│              🗑️                             │
│                                             │
│         Your bin is empty                   │
│                                             │
│  Deleted posts will appear here and can     │
│  be restored within 30 days.                │
│                                             │
└─────────────────────────────────────────────┘
```

## Testing Checklist

- [x] Migration runs successfully
- [x] Soft delete works (post moves to bin)
- [x] Custom delete dialog appears
- [x] Bin page displays trashed posts
- [x] Days remaining calculated correctly
- [x] Warning badge shows for posts ≤7 days
- [x] Restore functionality works
- [x] Permanent delete works
- [x] Media files deleted on permanent delete
- [ ] Scheduled task runs and cleans up old posts
- [ ] Empty state shows when no trashed posts
- [ ] Bin link appears in sidebar

## Benefits

1. **Safety**: Accidental deletions can be recovered
2. **User-Friendly**: Familiar Facebook-style UX
3. **Automatic Cleanup**: No manual maintenance needed
4. **Transparent**: Users see exactly when posts expire
5. **Flexible**: Can restore or force delete anytime
6. **Professional**: Matches industry standards

## Future Enhancements

- [ ] Bulk restore/delete actions
- [ ] Search/filter trashed posts
- [ ] Sort by deletion date or expiry date
- [ ] Email notifications before permanent deletion
- [ ] Restore with original timestamp
- [ ] Admin view of all trashed posts across charities
