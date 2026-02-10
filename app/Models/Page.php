<?php

namespace App\Models;

use App\Services\ContentImageService;
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
        'menu_order',
        'parent_id',
    ];

    protected $casts = [
        'title' => 'array',
        'content' => 'array',
        'sections' => 'array',
        'is_active' => 'boolean',
        'menu_order' => 'integer',
        'parent_id' => 'integer',
    ];

    /**
     * Parent page (for submenu).
     */
    public function parent()
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    /**
     * Child pages (submenu items).
     */
    public function children()
    {
        return $this->hasMany(Page::class, 'parent_id')->orderBy('menu_order')->orderBy('id');
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
     * Scope: top-level menu items (no parent).
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Generate slug from title
     */
    public static function generateSlug($title, $languageCode = 'en')
    {
        $baseTitle = is_array($title) ? ($title[$languageCode] ?? reset($title)) : $title;
        return Str::slug($baseTitle, '_');
    }

    /**
     * Delete section images from storage when page is deleted.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Page $page) {
            $sections = $page->sections;
            if (is_array($sections)) {
                $paths = ContentImageService::extractImagePathsFromPageSections($sections);
                ContentImageService::deletePaths($paths);
            }
        });
    }
}
