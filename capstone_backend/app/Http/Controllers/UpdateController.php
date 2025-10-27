<?php

namespace App\Http\Controllers;

use App\Models\Update;
use App\Models\UpdateLike;
use App\Models\UpdateComment;
use App\Models\UpdateShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UpdateController extends Controller
{
    /**
     * Get all updates for the authenticated charity
     * IMPORTANT: Only returns updates created by the logged-in charity
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            
            if (!$user || $user->role !== 'charity_admin' || !$user->charity) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $charityId = $user->charity->id;

            // Filter: Only show updates authored by this charity
            $updates = Update::where('charity_id', $charityId)
                ->whereNull('parent_id') // Exclude threaded replies
                ->with(['charity'])
                ->orderBy('is_pinned', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            // Add is_liked flag for authenticated users
            $updates->each(function ($update) use ($user) {
                $update->is_liked = $update->isLikedBy($user->id);
                if ($update->children) {
                    $update->children->each(function ($child) use ($user) {
                        $child->is_liked = $child->isLikedBy($user->id);
                    });
                }
            });

            return response()->json(['data' => $updates]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch updates: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch updates', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all updates for a specific charity (public endpoint)
     */
    public function getCharityUpdates($charityId)
    {
        try {
            $updates = Update::where('charity_id', $charityId)
                ->whereNull('parent_id')
                ->with(['charity', 'children'])
                ->orderBy('is_pinned', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            // Convert to array and manually add is_liked
            $token = request()->bearerToken();
            Log::info('getCharityUpdates - Token received: ' . ($token ? 'YES (length: ' . strlen($token) . ')' : 'NO'));
            
            $user = auth()->user();
            Log::info('getCharityUpdates - User ID: ' . ($user ? $user->id : 'NULL'));
            
            if (!$user && $token) {
                Log::error('Token present but user is NULL - token might be invalid or expired');
            }
            
            $updatesArray = $updates->map(function ($update) use ($user) {
                $updateArray = $update->toArray();
                $isLiked = $user ? $update->isLikedBy($user->id) : false;
                Log::info("Update {$update->id} - is_liked: " . ($isLiked ? 'TRUE' : 'FALSE'));
                $updateArray['is_liked'] = $isLiked;
                
                // Add is_liked for children
                if (isset($updateArray['children']) && is_array($updateArray['children'])) {
                    $updateArray['children'] = array_map(function ($child) use ($user, $update) {
                        $childModel = $update->children->firstWhere('id', $child['id']);
                        $child['is_liked'] = $user && $childModel ? $childModel->isLikedBy($user->id) : false;
                        return $child;
                    }, $updateArray['children']);
                }
                
                return $updateArray;
            });

            return response()->json(['data' => $updatesArray]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch charity updates: ' . $e->getMessage());
            return response()->json(['data' => []], 200);
        }
    }

    /**
     * Create a new update
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            
            if ($user->role !== 'charity_admin' || !$user->charity) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'content' => 'required|string|max:5000',
                'parent_id' => 'nullable|exists:updates,id',
                'media' => 'nullable|array|max:4',
                'media.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            ]);

            $mediaUrls = [];

            // Handle media uploads
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $path = $file->store('updates', 'public');
                    $mediaUrls[] = $path;
                }
            }

            // Verify parent_id belongs to same charity if provided
            if (isset($validated['parent_id'])) {
                $parent = Update::find($validated['parent_id']);
                if ($parent && $parent->charity_id !== $user->charity->id) {
                    return response()->json(['error' => 'Cannot thread to another charity\'s update'], 403);
                }
            }

            $update = Update::create([
                'charity_id' => $user->charity->id,
                'parent_id' => $validated['parent_id'] ?? null,
                'content' => $validated['content'],
                'media_urls' => $mediaUrls,
            ]);

            $update->load('charity');

            return response()->json($update, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create update: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create update'], 500);
        }
    }

    /**
     * Update an existing update
     */
    public function update(Request $request, $id)
    {
        try {
            $update = Update::findOrFail($id);
            $user = auth()->user();

            // Check ownership
            if ($user->role !== 'charity_admin' || !$user->charity || $update->charity_id !== $user->charity->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'content' => 'required|string|max:5000',
            ]);

            $update->update(['content' => $validated['content']]);

            return response()->json($update);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update'], 500);
        }
    }

    /**
     * Soft delete an update (move to bin)
     */
    public function destroy($id)
    {
        try {
            $update = Update::findOrFail($id);
            $user = auth()->user();

            // Check ownership
            if ($user->role !== 'charity_admin' || !$user->charity || $update->charity_id !== $user->charity->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Soft delete (moves to bin)
            $update->delete();

            return response()->json([
                'message' => 'Post moved to bin. It will be permanently deleted after 30 days.',
                'deleted_at' => $update->deleted_at
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete update: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete update'], 500);
        }
    }

    /**
     * Get trashed updates (bin)
     */
    public function getTrashed()
    {
        try {
            $user = auth()->user();
            
            if ($user->role !== 'charity_admin' || !$user->charity) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $trashedUpdates = Update::onlyTrashed()
                ->where('charity_id', $user->charity->id)
                ->whereNull('parent_id')
                ->orderBy('deleted_at', 'desc')
                ->get();

            return response()->json(['data' => $trashedUpdates]);
        } catch (\Exception $e) {
            Log::error('Failed to get trashed updates: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get trashed updates'], 500);
        }
    }

    /**
     * Restore a trashed update
     */
    public function restore($id)
    {
        try {
            $update = Update::onlyTrashed()->findOrFail($id);
            $user = auth()->user();

            // Check ownership
            if ($user->role !== 'charity_admin' || !$user->charity || $update->charity_id !== $user->charity->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $update->restore();

            return response()->json([
                'message' => 'Post restored successfully',
                'update' => $update
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to restore update: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to restore update'], 500);
        }
    }

    /**
     * Permanently delete a trashed update
     */
    public function forceDelete($id)
    {
        try {
            $update = Update::onlyTrashed()->findOrFail($id);
            $user = auth()->user();

            // Check ownership
            if ($user->role !== 'charity_admin' || !$user->charity || $update->charity_id !== $user->charity->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Delete associated media files
            if ($update->media_urls) {
                foreach ($update->media_urls as $mediaUrl) {
                    Storage::disk('public')->delete($mediaUrl);
                }
            }

            $update->forceDelete();

            return response()->json(['message' => 'Post permanently deleted']);
        } catch (\Exception $e) {
            Log::error('Failed to permanently delete update: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to permanently delete update'], 500);
        }
    }

    /**
     * Toggle pin status
     */
    public function togglePin($id)
    {
        try {
            $update = Update::findOrFail($id);
            $user = auth()->user();

            // Check ownership
            if ($user->role !== 'charity_admin' || !$user->charity || $update->charity_id !== $user->charity->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Only allow pinning root updates (not threaded replies)
            if ($update->parent_id) {
                return response()->json(['error' => 'Cannot pin threaded updates'], 400);
            }

            $update->update(['is_pinned' => !$update->is_pinned]);

            return response()->json($update);
        } catch (\Exception $e) {
            Log::error('Failed to toggle pin: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to toggle pin'], 500);
        }
    }

    /**
     * Toggle like on an update
     */
    public function toggleLike($id)
    {
        try {
            $update = Update::findOrFail($id);
            $userId = auth()->id();
            
            Log::info("toggleLike called for update {$id} by user {$userId}");

            $like = UpdateLike::where('update_id', $id)
                ->where('user_id', $userId)
                ->first();

            if ($like) {
                // Unlike
                Log::info("Unliking update {$id} for user {$userId}");
                $like->delete();
                $update->decrement('likes_count');
                $liked = false;
            } else {
                // Like
                Log::info("Liking update {$id} for user {$userId}");
                $newLike = UpdateLike::create([
                    'update_id' => $id,
                    'user_id' => $userId,
                ]);
                Log::info("Like created with ID: {$newLike->id}");
                $update->increment('likes_count');
                $liked = true;
            }

            return response()->json([
                'liked' => $liked,
                'likes_count' => $update->fresh()->likes_count
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to toggle like: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to toggle like'], 500);
        }
    }

    /**
     * Get comments for an update
     */
    public function getComments($id)
    {
        try {
            $userId = auth()->id();
            $comments = UpdateComment::where('update_id', $id)
                ->with(['user:id,name,role,profile_image', 'user.charity:id,owner_id,name,logo_path'])
                ->where('is_hidden', false)
                ->orderBy('created_at', 'asc')
                ->get();

            // Add is_liked flag and likes_count for each comment
            if ($userId) {
                try {
                    $likedCommentIds = \App\Models\CommentLike::where('user_id', $userId)
                        ->whereIn('comment_id', $comments->pluck('id'))
                        ->pluck('comment_id')
                        ->toArray();
                } catch (\Exception $e) {
                    // Table might not exist yet
                    $likedCommentIds = [];
                }

                $comments->each(function ($comment) use ($likedCommentIds) {
                    $comment->is_liked = in_array($comment->id, $likedCommentIds);
                    // Ensure likes_count exists (for backward compatibility)
                    if (!isset($comment->likes_count)) {
                        $comment->likes_count = 0;
                    }
                });
            } else {
                $comments->each(function ($comment) {
                    $comment->is_liked = false;
                    // Ensure likes_count exists (for backward compatibility)
                    if (!isset($comment->likes_count)) {
                        $comment->likes_count = 0;
                    }
                });
            }

            return response()->json(['data' => $comments]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch comments: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch comments'], 500);
        }
    }

    /**
     * Add a comment to an update
     */
    public function addComment(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'content' => 'required|string|max:1000',
            ]);

            $update = Update::findOrFail($id);

            $comment = UpdateComment::create([
                'update_id' => $id,
                'user_id' => auth()->id(),
                'content' => $validated['content'],
            ]);

            $update->increment('comments_count');

            $comment->load(['user:id,name,role,profile_image', 'user.charity:id,owner_id,name,logo_path']);

            return response()->json($comment, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to add comment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to add comment'], 500);
        }
    }

    /**
     * Update/Edit a comment
     */
    public function updateComment(Request $request, $id)
    {
        try {
            $comment = UpdateComment::findOrFail($id);
            $user = auth()->user();

            // Only comment owner can edit
            if ($comment->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'content' => 'required|string|max:1000',
            ]);

            $comment->update([
                'content' => $validated['content'],
            ]);

            $comment->load(['user:id,name,role,profile_image', 'user.charity:id,owner_id,name,logo_path']);

            return response()->json($comment);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update comment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update comment'], 500);
        }
    }

    /**
     * Delete a comment
     */
    public function deleteComment($id)
    {
        try {
            $comment = UpdateComment::findOrFail($id);
            $user = auth()->user();

            // Allow deletion by comment owner or charity admin of the update
            $update = $comment->updatePost;
            $isOwner = $comment->user_id === $user->id;
            $isCharityAdmin = $user->role === 'charity_admin' && 
                              $user->charity && 
                              $update->charity_id === $user->charity->id;

            if (!$isOwner && !$isCharityAdmin) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $updateId = $comment->update_id;
            $comment->delete();

            Update::find($updateId)->decrement('comments_count');

            return response()->json(['message' => 'Comment deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to delete comment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete comment'], 500);
        }
    }

    /**
     * Toggle like on a comment
     */
    public function toggleCommentLike($id)
    {
        try {
            $comment = UpdateComment::findOrFail($id);
            $user = auth()->user();

            $like = \App\Models\CommentLike::where('comment_id', $id)
                ->where('user_id', $user->id)
                ->first();

            if ($like) {
                // Unlike
                $like->delete();
                $comment->decrement('likes_count');
                $isLiked = false;
            } else {
                // Like
                \App\Models\CommentLike::create([
                    'comment_id' => $id,
                    'user_id' => $user->id,
                ]);
                $comment->increment('likes_count');
                $isLiked = true;
            }

            $comment->refresh();

            return response()->json([
                'likes_count' => $comment->likes_count,
                'is_liked' => $isLiked,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to toggle comment like: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to toggle like'], 500);
        }
    }

    /**
     * Hide a comment (charity admin only)
     */
    public function hideComment($id)
    {
        try {
            $comment = UpdateComment::findOrFail($id);
            $user = auth()->user();

            // Only charity admin of the update can hide comments
            $update = $comment->updatePost;
            if ($user->role !== 'charity_admin' || !$user->charity || $update->charity_id !== $user->charity->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $comment->is_hidden = !$comment->is_hidden;
            $comment->save();

            return response()->json($comment);
        } catch (\Exception $e) {
            Log::error('Failed to hide comment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to hide comment'], 500);
        }
    }

    /**
     * Track share action
     */
    public function shareUpdate(Request $request, $id)
    {
        try {
            $update = Update::findOrFail($id);
            $userId = auth()->id();
            
            $validated = $request->validate([
                'platform' => 'nullable|string|max:50',
            ]);

            UpdateShare::create([
                'update_id' => $id,
                'user_id' => $userId,
                'platform' => $validated['platform'] ?? 'copy_link',
            ]);

            $update->increment('shares_count');

            return response()->json([
                'success' => true,
                'shares_count' => $update->fresh()->shares_count,
                'message' => 'Share tracked successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to track share: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to track share'], 500);
        }
    }
}
