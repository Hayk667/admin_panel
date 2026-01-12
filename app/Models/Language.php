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

