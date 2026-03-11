<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * List active posts for API.
     * Returns: title, content, created user name, likes, rate, views, category name, thumbnail, created date.
     * Title, content, and category_name are returned in all languages (keyed by language code).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Post::query()
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['category', 'createdUser'])
            ->orderBy('published_at', 'desc');

        $perPage = (int) $request->input('per_page', 15);
        $perPage = $perPage >= 1 && $perPage <= 100 ? $perPage : 15;
        $posts = $query->paginate($perPage);

        $items = $posts->getCollection()->map(function (Post $post) {
            return [
                'id' => $post->id,
                'slug' => $post->slug,
                'title' => $post->title ?? [],
                'content' => $post->content ?? [],
                'created_user_name' => $post->createdUser?->name,
                'likes' => (int) $post->likes,
                'rate' => $post->rate !== null ? (float) $post->rate : null,
                'views' => (int) $post->view_count,
                'category_name' => $post->category?->name ?? [],
                'thumbnail' => $post->thumbnail ? url('storage/' . $post->thumbnail) : null,
                'published_at' => $post->published_at?->format('Y-m-d, H:i'),
            ];
        });

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'from' => $posts->firstItem(),
                'to' => $posts->lastItem(),
            ],
        ]);
    }
}
