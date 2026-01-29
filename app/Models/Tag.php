<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Get the posts that have this tag.
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_tag');
    }

    /**
     * Generate slug from name.
     */
    public static function generateSlug(string $name): string
    {
        return Str::slug($name, '_');
    }

    /**
     * Boot the model - auto-generate slug when creating.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = self::generateSlug($tag->name);
            }
        });
    }
}
