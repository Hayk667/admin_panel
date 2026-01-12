<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    /**
     * Display the home page with posts.
     */
    public function index(): View
    {
        $defaultLang = Language::getDefault();
        $langCode = $defaultLang ? $defaultLang->code : 'en';
        
        $posts = Post::where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with('category')
            ->orderBy('published_at', 'desc')
            ->paginate(4);
        
        return view('welcome', compact('posts', 'langCode'));
    }

    /**
     * Display a single post publicly.
     */
    public function show(string $slug): View
    {
        $defaultLang = Language::getDefault();
        $langCode = $defaultLang ? $defaultLang->code : 'en';
        
        $post = Post::where('slug', $slug)
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['category', 'createdUser'])
            ->firstOrFail();
        
        // Increment view count
        $post->increment('view_count');
        
        // Check if user has liked/rated this post (using session)
        $hasLiked = session("post_liked_{$post->id}", false);
        $hasRated = session("post_rated_{$post->id}", false);
        $userRating = session("post_rating_{$post->id}", 0);
        
        // Get last 5 posts for sidebar
        $recentPosts = Post::where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('id', '!=', $post->id)
            ->with('category')
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('post', compact('post', 'recentPosts', 'langCode', 'hasLiked', 'hasRated', 'userRating'));
    }

    /**
     * Like a post.
     */
    public function like(Post $post): JsonResponse
    {
        // Check if already liked
        if (session("post_liked_{$post->id}", false)) {
            return response()->json([
                'success' => false,
                'message' => 'You have already liked this post'
            ], 400);
        }
        
        $post->increment('likes');
        
        // Mark as liked in session
        session(["post_liked_{$post->id}" => true]);
        
        return response()->json([
            'success' => true,
            'likes' => $post->fresh()->likes
        ]);
    }

    /**
     * Rate a post.
     */
    public function rate(Request $request, Post $post): JsonResponse
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5'
        ]);
        
        // Check if already rated
        if (session("post_rated_{$post->id}", false)) {
            return response()->json([
                'success' => false,
                'message' => 'You have already rated this post'
            ], 400);
        }
        
        $rating = $request->rating;
        $currentRate = $post->rate ?? 0;
        $ratingCount = $post->rating_count ?? 0;
        
        // Calculate new average rate
        if ($ratingCount == 0) {
            $newRate = $rating;
        } else {
            $newRate = (($currentRate * $ratingCount) + $rating) / ($ratingCount + 1);
        }
        
        $post->update([
            'rate' => round($newRate, 2),
            'rating_count' => $ratingCount + 1
        ]);
        
        // Mark as rated in session and store the rating
        session([
            "post_rated_{$post->id}" => true,
            "post_rating_{$post->id}" => $rating
        ]);
        
        return response()->json([
            'success' => true,
            'rate' => $post->fresh()->rate,
            'rating' => $rating
        ]);
    }
}

