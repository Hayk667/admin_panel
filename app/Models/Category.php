<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get posts for this category
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get name for specific language
     */
    public function getName($languageCode = null)
    {
        $languageCode = $languageCode ?? Language::getDefault()?->code ?? 'en';
        return $this->name[$languageCode] ?? $this->name['en'] ?? '';
    }

    /**
     * Generate slug from name
     */
    public static function generateSlug($name, $languageCode = 'en')
    {
        $baseName = is_array($name) ? ($name[$languageCode] ?? reset($name)) : $name;
        // Generate slug with underscores instead of hyphens
        $slug = Str::slug($baseName, '_');
        return $slug;
    }
}

