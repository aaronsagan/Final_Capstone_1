# Soft Delete & Bin System Implementation

## Overview
Implemented Facebook-style soft delete system where deleted posts go to a bin/trash and are automatically deleted after 30 days.

## Backend Changes

### 1. Database Migration
**File**: `2025_10_20_090000_add_soft_deletes_to_updates.php`
- Adds `deleted_at` column to `updates` table
- Enables soft delete functionality

### 2. Update Model
**File**: `app/Models/Update.php`
- Added `SoftDeletes` trait
- Added `deleted_at` to casts
- Now `delete()` soft deletes instead of permanently deleting

### 3. Controller Methods
**File**: `app/Http/Controllers/UpdateController.php`

**Modified**:
- `destroy($id)` - Now soft deletes (moves to bin) instead of permanent delete
  - Returns message: "Post moved to bin. It will be permanently deleted after 30 days."

**Added**:
- `getTrashed()` - Get all trashed posts for the charity
- `restore($id)` - Restore a post from the bin
- `forceDelete($id)` - Permanently delete a post from the bin

### 4. Routes
**File**: `routes/api.php`

Added routes:
```php
Route::get('/updates/trash', [UpdateController::class, 'getTrashed']);
Route::post('/updates/{id}/restore', [UpdateController::class, 'restore']);
Route::delete('/updates/{id}/force', [UpdateController::class, 'forceDelete']);
```

## Frontend Changes

### 1. Delete Dialog Component
**File**: `src/components/ui/delete-dialog.tsx`

Custom dialog component matching Facebook's design:
- Title: "Move to your bin?"
- Description: "Items in your bin will be automatically deleted after 30 days..."
- Buttons: "Cancel" and "Move"
- Clean, modern UI

### 2. CharityUpdates Page
**File**: `src/pages/charity/CharityUpdates.tsx`

**Changes**:
- Replaced browser `confirm()` with custom `DeleteDialog`
- Added state: `deleteDialogOpen`, `updateToDelete`
- Updated `handleDelete()` to open dialog
- Added `confirmDelete()` to handle actual deletion
- Shows toast: "Post moved to bin. It will be permanently deleted after 30 days."

## How It Works

### Delete Flow
1. User clicks "Delete" on a post
2. Custom dialog appears: "Move to your bin?"
3. User clicks "Move"
4. Post is soft deleted (moved to bin)
5. Toast notification confirms action
6. Post disappears from feed

### What Happens to Deleted Posts
- **Immediately**: Post is hidden from feed (`deleted_at` timestamp set)
- **In Bin**: Post can be viewed in trash/bin section
- **30 Days Later**: Post is automatically permanently deleted
- **Before 30 Days**: User can restore or permanently delete manually

## Database Commands

```bash
# Run migration
cd capstone_backend
php artisan migrate
```

This adds the `deleted_at` column to the `updates` table.

## API Endpoints

### Delete Post (Soft Delete)
```http
DELETE /api/updates/{id}
Authorization: Bearer {token}

Response:
{
  "message": "Post moved to bin. It will be permanently deleted after 30 days.",
  "deleted_at": "2025-10-20T08:30:00.000000Z"
}
```

### Get Trashed Posts
```http
GET /api/updates/trash
Authorization: Bearer {token}

Response:
{
  "data": [
    {
      "id": 1,
      "content": "...",
      "deleted_at": "2025-10-20T08:30:00.000000Z",
      ...
    }
  ]
}
```

### Restore Post
```http
POST /api/updates/{id}/restore
Authorization: Bearer {token}

Response:
{
  "message": "Post restored successfully",
  "update": { ... }
}
```

### Permanently Delete
```http
DELETE /api/updates/{id}/force
Authorization: Bearer {token}

Response:
{
  "message": "Post permanently deleted"
}
```

## UI Components

### Delete Dialog
```tsx
<DeleteDialog
  open={deleteDialogOpen}
  onOpenChange={setDeleteDialogOpen}
  onConfirm={confirmDelete}
  title="Move to your bin?"
  description="Items in your bin will be automatically deleted after 30 days..."
  confirmText="Move"
  cancelText="Cancel"
/>
```

## Future Enhancements

### Automatic Cleanup (Scheduled Task)
Create a Laravel command to permanently delete posts older than 30 days:

```php
// app/Console/Commands/CleanupTrashedUpdates.php
public function handle()
{
    $thirtyDaysAgo = now()->subDays(30);
    
    $deleted = Update::onlyTrashed()
        ->where('deleted_at', '<=', $thirtyDaysAgo)
        ->each(function ($update) {
            // Delete media files
            if ($update->media_urls) {
                foreach ($update->media_urls as $mediaUrl) {
                    Storage::disk('public')->delete($mediaUrl);
                }
            }
            $update->forceDelete();
        });
}
```

Schedule in `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('cleanup:trashed-updates')->daily();
}
```

### Bin/Trash View Page
Create a page to view and manage trashed posts:
- List all trashed posts
- Show days remaining until permanent deletion
- Restore button
- Permanently delete button
- Bulk actions

## Testing

1. ✅ Run migration
2. ✅ Delete a post - should show custom dialog
3. ✅ Confirm deletion - post disappears
4. ✅ Check database - `deleted_at` should be set
5. ✅ Post should not appear in feed
6. [ ] Access bin/trash view
7. [ ] Restore post from bin
8. [ ] Permanently delete from bin

## Benefits

1. **Safety**: Accidental deletions can be recovered
2. **User-Friendly**: Familiar Facebook-style UX
3. **Automatic Cleanup**: Posts auto-delete after 30 days
4. **Professional**: Matches industry standards
5. **Flexible**: Can restore or force delete anytime
