<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Services\ContentImageService;

class Post extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'slug',
        'category_id',
        'created_user_id',
        'updated_user_id',
        'published_at',
        'image',
        'thumbnail',
        'title',
        'content',
        'is_active',
        'likes',
        'rate',
        'rating_count',
        'view_count',
    ];

    protected $casts = [
        'title' => 'array',
        'content' => 'array',
        'published_at' => 'date',
        'is_active' => 'boolean',
        'likes' => 'integer',
        'rate' => 'decimal:2',
        'rating_count' => 'integer',
        'view_count' => 'integer',
    ];

    /**
     * Get the category that owns the post
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the user who created the post
     */
    public function createdUser()
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    /**
     * Get the user who last updated the post
     */
    public function updatedUser()
    {
        return $this->belongsTo(User::class, 'updated_user_id');
    }

    /**
     * Get the tags for the post.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }

    /**
     * Get title for specific language
     */
    public function getTitle($languageCode = null)
    {
        $languageCode = $languageCode ?? Language::getDefault()?->code ?? 'en';
        return $this->title[$languageCode] ?? $this->title['en'] ?? '';
    }

    /**
     * Get content for specific language
     */
    public function getContent($languageCode = null)
    {
        $languageCode = $languageCode ?? Language::getDefault()?->code ?? 'en';
        return $this->content[$languageCode] ?? $this->content['en'] ?? '';
    }

    /**
     * Scope: search in title and content across all languages (JSON columns).
     */
    public function scopeSearch($query, string $term)
    {
        if ($term === '') {
            return $query;
        }
        $like = '%' . addcslashes($term, '%_\\') . '%';
        return $query->where(function ($q) use ($like) {
            $q->where('title', 'like', $like)->orWhere('content', 'like', $like);
        });
    }

    /**
     * Generate slug from title
     */
    public static function generateSlug($title, $languageCode = 'en')
    {
        $baseTitle = is_array($title) ? ($title[$languageCode] ?? reset($title)) : $title;
        // Generate slug with underscores instead of hyphens
        $slug = Str::slug($baseTitle, '_');
        return $slug;
    }

    /**
     * Delete image, thumbnail and content images only when post is force (permanently) deleted.
     * Soft delete keeps images so the post can be restored.
     */
    protected static function boot()
    {
        parent::boot();

        static::forceDeleting(function ($post) {
            if ($post->image && Storage::disk('public')->exists($post->image)) {
                Storage::disk('public')->delete($post->image);
            }
            if ($post->thumbnail && Storage::disk('public')->exists($post->thumbnail)) {
                Storage::disk('public')->delete($post->thumbnail);
            }
            // Delete images embedded in content (TinyMCE uploads)
            $content = $post->content;
            if (is_array($content)) {
                $paths = ContentImageService::extractImagePathsFromPostContent($content);
                ContentImageService::deletePaths($paths);
            }
        });
    }
}

