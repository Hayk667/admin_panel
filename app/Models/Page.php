<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'title',
        'content',
        'sections',
        'is_active',
    ];

    protected $casts = [
        'title' => 'array',
        'content' => 'array',
        'sections' => 'array',
        'is_active' => 'boolean',
    ];

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
     * Generate slug from title
     */
    public static function generateSlug($title, $languageCode = 'en')
    {
        $baseTitle = is_array($title) ? ($title[$languageCode] ?? reset($title)) : $title;
        return Str::slug($baseTitle, '_');
    }
}
