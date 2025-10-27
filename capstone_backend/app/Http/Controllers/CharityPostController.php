<?php

namespace App\Http\Controllers;

use App\Models\{CharityPost, Charity, CharityFollow};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;

class CharityPostController extends Controller
{
    // Get all published posts for news feed (public)
    public function index()
    {
        $posts = CharityPost::with(['charity' => function($query) {
            $query->where('verification_status', 'approved');
        }])
        ->published()
        ->recent()
        ->paginate(10);

        return response()->json($posts);
    }

    // Get posts for specific charity (public)
    public function getCharityPosts($charityId)
    {
        $charity = Charity::where('id', $charityId)
                         ->where('verification_status', 'approved')
                         ->firstOrFail();

        $posts = CharityPost::where('charity_id', $charityId)
                           ->published()
                           ->recent()
                           ->paginate(10);

        return response()->json($posts);
    }

    // Get posts for charity admin's own charity
    public function getMyPosts()
    {
        $user = auth()->user();
        $charity = Charity::where('owner_id', $user->id)->firstOrFail();

        $posts = CharityPost::where('charity_id', $charity->id)
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);

        return response()->json($posts);
    }

    // Create new post (charity admin only)
    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $charity = Charity::where('owner_id', $user->id)->first();
            
            if (!$charity) {
                return response()->json(['error' => 'No charity found for this user'], 404);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'status' => 'required|in:draft,published'
            ]);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('charity-posts', 'public');
            }

            $post = CharityPost::create([
                'charity_id' => $charity->id,
                'title' => $validated['title'],
                'content' => $validated['content'],
                'image_path' => $imagePath,
                'status' => $validated['status'],
                'published_at' => $validated['status'] === 'published' ? now() : null
            ]);

            // Send notifications to followers if post is published
            if ($validated['status'] === 'published') {
                $this->sendPostNotifications($post, $charity);
            }

            return response()->json($post->load('charity'), 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create post',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Update post (charity admin only)
    public function update(Request $request, CharityPost $post)
    {
        $user = auth()->user();
        $charity = Charity::where('owner_id', $user->id)->firstOrFail();

        if ($post->charity_id !== $charity->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:draft,published'
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($post->image_path) {
                Storage::disk('public')->delete($post->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('charity-posts', 'public');
        }

        // Set published_at when publishing
        if ($validated['status'] === 'published' && $post->status !== 'published') {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        return response()->json($post->load('charity'));
    }

    // Delete post (charity admin only)
    public function destroy(CharityPost $post)
    {
        $user = auth()->user();
        $charity = Charity::where('owner_id', $user->id)->firstOrFail();

        if ($post->charity_id !== $charity->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Delete image file
        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }

    private function sendPostNotifications($post, $charity)
    {
        try {
            // Get all followers of this charity
            $followers = CharityFollow::where('charity_id', $charity->id)
                ->where('is_following', true)
                ->with('donor')
                ->get();

            foreach ($followers as $follow) {
                if ($follow->donor) {
                    Notification::create([
                        'user_id' => $follow->donor->id,
                        'type' => 'charity_post',
                        'title' => 'New Update from ' . $charity->name,
                        'message' => "Check out the latest update: {$post->title}",
                        'data' => [
                            'charity_id' => $charity->id,
                            'post_id' => $post->id,
                            'post_title' => $post->title,
                        ]
                    ]);
                }
            }

            Log::info("Post notifications sent to {$followers->count()} followers of {$charity->name}");

        } catch (\Exception $e) {
            Log::error('Failed to send post notifications: ' . $e->getMessage());
        }
    }
}
