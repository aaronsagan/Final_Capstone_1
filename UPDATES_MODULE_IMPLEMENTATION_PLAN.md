# Updates Module Implementation Plan

## Overview
This document outlines the complete implementation of the Updates module to replace the Posts system with threading functionality.

## Backend Requirements

### Database Migrations Needed

```sql
-- Create updates table
CREATE TABLE updates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    charity_id BIGINT UNSIGNED NOT NULL,
    parent_id BIGINT UNSIGNED NULL,
    content TEXT NOT NULL,
    media_urls JSON NULL,
    is_pinned BOOLEAN DEFAULT FALSE,
    likes_count INT DEFAULT 0,
    comments_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (charity_id) REFERENCES charities(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES updates(id) ON DELETE CASCADE
);

-- Create update_likes table
CREATE TABLE update_likes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    update_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (update_id) REFERENCES updates(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (update_id, user_id)
);

-- Create update_comments table
CREATE TABLE update_comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    update_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    content TEXT NOT NULL,
    is_hidden BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (update_id) REFERENCES updates(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### API Endpoints Required

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/updates` | Get all updates for logged-in charity | charity_admin |
| GET | `/api/updates?charity_id={id}` | Get updates for specific charity (public) | any |
| POST | `/api/updates` | Create new update | charity_admin |
| PUT | `/api/updates/{id}` | Edit update | charity_admin |
| DELETE | `/api/updates/{id}` | Delete update | charity_admin |
| POST | `/api/updates/{id}/pin` | Toggle pin status | charity_admin |
| POST | `/api/updates/{id}/like` | Like/unlike update | donor |
| GET | `/api/updates/{id}/comments` | Get comments for update | any |
| POST | `/api/updates/{id}/comments` | Add comment | authenticated |
| DELETE | `/api/comments/{id}` | Delete comment | owner/admin |

### Controller Methods Needed

```php
// UpdateController.php
class UpdateController extends Controller
{
    public function index(Request $request)
    {
        $charityId = $request->charity_id ?? auth()->user()->charity->id;
        $updates = Update::where('charity_id', $charityId)
            ->with(['charity', 'children'])
            ->whereNull('parent_id') // Only root updates
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json(['data' => $updates]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:updates,id',
            'media' => 'nullable|array|max:4',
            'media.*' => 'image|max:5120'
        ]);

        $charityId = auth()->user()->charity->id;
        $mediaUrls = [];

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('updates', 'public');
                $mediaUrls[] = $path;
            }
        }

        $update = Update::create([
            'charity_id' => $charityId,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
            'media_urls' => json_encode($mediaUrls)
        ]);

        return response()->json($update->load('charity'), 201);
    }

    public function update(Request $request, $id)
    {
        $update = Update::findOrFail($id);
        
        // Check ownership
        if ($update->charity_id !== auth()->user()->charity->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string'
        ]);

        $update->update($validated);
        return response()->json($update);
    }

    public function destroy($id)
    {
        $update = Update::findOrFail($id);
        
        if ($update->charity_id !== auth()->user()->charity->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $update->delete();
        return response()->json(['message' => 'Update deleted']);
    }

    public function togglePin($id)
    {
        $update = Update::findOrFail($id);
        
        if ($update->charity_id !== auth()->user()->charity->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $update->update(['is_pinned' => !$update->is_pinned]);
        return response()->json($update);
    }

    public function toggleLike($id)
    {
        $update = Update::findOrFail($id);
        $userId = auth()->id();

        $like = UpdateLike::where('update_id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($like) {
            $like->delete();
            $update->decrement('likes_count');
            $liked = false;
        } else {
            UpdateLike::create([
                'update_id' => $id,
                'user_id' => $userId
            ]);
            $update->increment('likes_count');
            $liked = true;
        }

        return response()->json(['liked' => $liked, 'likes_count' => $update->likes_count]);
    }

    public function getComments($id)
    {
        $comments = UpdateComment::where('update_id', $id)
            ->with('user')
            ->where('is_hidden', false)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['data' => $comments]);
    }

    public function addComment(Request $request, $id)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $comment = UpdateComment::create([
            'update_id' => $id,
            'user_id' => auth()->id(),
            'content' => $validated['content']
        ]);

        Update::find($id)->increment('comments_count');

        return response()->json($comment->load('user'), 201);
    }

    public function deleteComment($id)
    {
        $comment = UpdateComment::findOrFail($id);
        
        // Allow deletion by comment owner or charity admin
        if ($comment->user_id !== auth()->id() && auth()->user()->role !== 'charity_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        Update::find($comment->update_id)->decrement('comments_count');
        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }
}
```

## Frontend Implementation

### File Structure
```
src/
├── pages/
│   ├── charity/
│   │   └── CharityUpdates.tsx (NEW - replaces CharityPosts.tsx)
│   └── donor/
│       └── CommunityFeed.tsx (NEW - optional public feed)
├── components/
│   └── updates/
│       ├── UpdateCard.tsx (NEW)
│       ├── ThreadConnector.tsx (NEW)
│       └── CommentSection.tsx (NEW)
└── services/
    └── updates.ts (NEW)
```

### Key Features to Implement

1. **Create Update Box**
   - Textarea for content
   - Multi-image upload (max 4)
   - Image preview with remove option
   - Post button

2. **Thread Functionality**
   - "Add to Thread" button in dropdown
   - Visual connector line for child updates
   - Nested rendering with indentation
   - Parent-child relationship tracking

3. **Update Card**
   - Charity avatar and name
   - Timestamp
   - Pin badge (if pinned)
   - Content with media
   - Like/Comment buttons with counts
   - Dropdown menu (Edit, Pin, Add to Thread, Delete)

4. **Comments System**
   - Toggle expand/collapse
   - Fetch comments on expand
   - Add comment input
   - Delete comment button
   - Real-time count updates

5. **Edit Functionality**
   - Edit dialog modal
   - Update content
   - Save changes

6. **Like System**
   - Optimistic UI updates
   - Heart icon fill on like
   - Count display

### Route Updates

**Update App.tsx:**
```tsx
// Change from:
<Route path="/charity/posts" element={<CharityPosts />} />

// To:
<Route path="/charity/updates" element={<CharityUpdates />} />
```

**Update Navigation:**
- Sidebar: "Posts" → "Updates"
- Dashboard links: `/charity/posts` → `/charity/updates`

## Implementation Steps

### Phase 1: Backend Setup
1. Create migrations for `updates`, `update_likes`, `update_comments` tables
2. Create Update, UpdateLike, UpdateComment models
3. Create UpdateController with all methods
4. Add routes to `api.php`
5. Test all endpoints with Postman

### Phase 2: Frontend Core
1. Create `CharityUpdates.tsx` component
2. Implement create update functionality
3. Implement fetch and display updates
4. Add edit and delete functionality

### Phase 3: Threading
1. Implement parent_id logic
2. Create thread rendering with connectors
3. Add "Add to Thread" functionality
4. Test nested updates

### Phase 4: Interactions
1. Implement like/unlike functionality
2. Create comment system
3. Add comment CRUD operations
4. Test real-time updates

### Phase 5: Polish
1. Add loading states
2. Add empty states
3. Implement error handling
4. Add optimistic UI updates
5. Test responsive design

## Testing Checklist

- [ ] Create update without media
- [ ] Create update with 1-4 images
- [ ] Edit update content
- [ ] Delete update
- [ ] Pin/unpin update
- [ ] Create threaded update
- [ ] View thread with connector
- [ ] Like/unlike update
- [ ] Add comment
- [ ] Delete own comment
- [ ] View comments list
- [ ] Responsive design (mobile/tablet/desktop)
- [ ] Loading states work
- [ ] Empty states display
- [ ] Error handling works

## Security Considerations

1. **Authorization**: Verify charity ownership for CRUD operations
2. **Validation**: Sanitize all user inputs
3. **File Upload**: Validate file types and sizes
4. **Rate Limiting**: Prevent spam (comments, likes)
5. **SQL Injection**: Use parameterized queries
6. **XSS**: Escape user-generated content

## Performance Optimizations

1. **Lazy Loading**: Load comments only when expanded
2. **Pagination**: Implement for large update lists
3. **Image Optimization**: Compress uploaded images
4. **Caching**: Cache frequently accessed updates
5. **Eager Loading**: Load relationships to avoid N+1 queries

This is a comprehensive implementation plan. The actual code files will be created in the next steps.
