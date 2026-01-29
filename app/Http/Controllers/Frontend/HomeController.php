<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Language;
use App\Models\Tag;
use App\Models\PostLike;
use App\Models\PostRating;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

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
            ->with(['category', 'tags'])
            ->orderBy('published_at', 'desc')
            ->paginate(4);

        $tags = Tag::withCount('posts')->orderBy('name')->get();

        return view('frontend.welcome', compact('posts', 'langCode', 'tags'));
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
            ->with(['category', 'createdUser', 'tags'])
            ->firstOrFail();

        // Increment view count
        $post->increment('view_count');

        // Check if user has liked/rated this post (using database)
        $hasLiked = $this->hasLikedPost($post);
        $hasRated = $this->hasRatedPost($post);
        $userRating = $hasRated ? $this->getUserRating($post) : 0;

        // Get last 5 posts for sidebar
        $recentPosts = Post::where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('id', '!=', $post->id)
            ->with('category')
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();

        $tags = Tag::withCount('posts')->orderBy('name')->get();

        return view('frontend.post', compact('post', 'recentPosts', 'tags', 'langCode', 'hasLiked', 'hasRated', 'userRating'));
    }

    /**
     * Like a post.
     */
    public function like(Post $post): JsonResponse
    {
        // Check if already liked
        if ($this->hasLikedPost($post)) {
            return response()->json([
                'success' => false,
                'message' => 'You have already liked this post'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Double-check to prevent race conditions
            if ($this->hasLikedPost($post)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'You have already liked this post'
                ], 400);
            }

            // Create like record
            PostLike::create([
                'post_id' => $post->id,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $post->increment('likes');

            DB::commit();

            return response()->json([
                'success' => true,
                'likes' => $post->fresh()->likes
            ]);
        } catch (QueryException $e) {
            DB::rollBack();
            // Handle duplicate entry (race condition)
            if ($e->getCode() == 23000) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already liked this post'
                ], 400);
            }
            throw $e;
        }
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
        if ($this->hasRatedPost($post)) {
            return response()->json([
                'success' => false,
                'message' => 'You have already rated this post'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Double-check to prevent race conditions
            if ($this->hasRatedPost($post)) {
                DB::rollBack();
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

            // Create rating record
            PostRating::create([
                'post_id' => $post->id,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'rating' => $rating,
            ]);

            $post->update([
                'rate' => round($newRate, 2),
                'rating_count' => $ratingCount + 1
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'rate' => $post->fresh()->rate,
                'rating' => $rating
            ]);
        } catch (QueryException $e) {
            DB::rollBack();
            // Handle duplicate entry (race condition)
            if ($e->getCode() == 23000) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already rated this post'
                ], 400);
            }
            throw $e;
        }
    }

    /**
     * Check if current user/IP has liked the post
     */
    private function hasLikedPost(Post $post): bool
    {
        $userId = auth()->id();
        $ipAddress = request()->ip();
        $userAgent = request()->userAgent();

        if ($userId) {
            return PostLike::where('post_id', $post->id)
                ->where('user_id', $userId)
                ->exists();
        }

        return PostLike::where('post_id', $post->id)
            ->where('ip_address', $ipAddress)
            ->where('user_agent', $userAgent)
            ->exists();
    }

    /**
     * Check if current user/IP has rated the post
     */
    private function hasRatedPost(Post $post): bool
    {
        $userId = auth()->id();
        $ipAddress = request()->ip();
        $userAgent = request()->userAgent();

        if ($userId) {
            return PostRating::where('post_id', $post->id)
                ->where('user_id', $userId)
                ->exists();
        }

        return PostRating::where('post_id', $post->id)
            ->where('ip_address', $ipAddress)
            ->where('user_agent', $userAgent)
            ->exists();
    }

    /**
     * Get current user/IP rating for the post
     */
    private function getUserRating(Post $post): int
    {
        $userId = auth()->id();
        $ipAddress = request()->ip();
        $userAgent = request()->userAgent();

        if ($userId) {
            $rating = PostRating::where('post_id', $post->id)
                ->where('user_id', $userId)
                ->first();
        } else {
            $rating = PostRating::where('post_id', $post->id)
                ->where('ip_address', $ipAddress)
                ->where('user_agent', $userAgent)
                ->first();
        }

        return $rating ? $rating->rating : 0;
    }
}
