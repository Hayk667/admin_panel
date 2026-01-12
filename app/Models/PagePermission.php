<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_route',
        'page_name',
    ];

    /**
     * Get the roles that can access this page
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'page_permission_role');
    }
}

