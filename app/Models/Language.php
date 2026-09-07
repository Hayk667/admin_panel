<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Language extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'is_default',
        'is_active',
    ];

    public const FRONTEND_LOCALE_KEY = 'frontend_locale';

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the default language
     */
    public static function getDefault()
    {
        return static::where('is_default', true)->where('is_active', true)->first();
    }

    /**
     * Active languages for the frontend switcher.
     */
    public static function getActive()
    {
        return static::where('is_active', true)->orderByDesc('is_default')->orderBy('code')->get();
    }

    /**
     * Current frontend language code (session/cookie via middleware, else default).
     */
    public static function currentCode(): string
    {
        $locale = app()->getLocale();

        if (is_string($locale) && $locale !== '' && static::where('code', $locale)->where('is_active', true)->exists()) {
            return $locale;
        }

        return static::getDefault()?->code ?? 'en';
    }

    /**
     * Set this language as default
     */
    public function setAsDefault()
    {
        // Remove default from all languages
        static::where('is_default', true)->update(['is_default' => false]);
        
        // Set this as default
        $this->update(['is_default' => true]);
    }
}

