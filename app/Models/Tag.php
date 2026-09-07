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
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the posts that have this tag.
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_tag');
    }

    /**
     * Get name for specific language.
     */
    public function getName($languageCode = null)
    {
        $languageCode = $languageCode ?? Language::getDefault()?->code ?? 'en';
        $name = $this->name;

        if (is_string($name)) {
            return $name;
        }

        return $name[$languageCode] ?? $name['en'] ?? '';
    }

    /**
     * Generate slug from name.
     */
    public static function generateSlug($name, $languageCode = 'en'): string
    {
        $baseName = is_array($name) ? ($name[$languageCode] ?? reset($name)) : $name;

        return Str::slug($baseName, '_');
    }

    /**
     * Boot the model - auto-generate slug when creating.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $langCode = Language::getDefault()?->code ?? 'en';
                $tag->slug = self::generateSlug($tag->name, $langCode);
            }
        });
    }
}
