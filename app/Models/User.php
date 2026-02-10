<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Delete profile photo from storage when user is permanently (force) deleted.
     */
    protected static function boot()
    {
        parent::boot();

        static::forceDeleting(function (User $user) {
            if ($user->profile_photo_path) {
                $disk = method_exists($user, 'profilePhotoDisk') ? $user->profilePhotoDisk() : 'public';
                Storage::disk($disk)->delete($user->profile_photo_path);
            }
        });
    }

    /**
     * Get the role that belongs to the user
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission($permissionSlug)
    {
        if (!$this->role) {
            return false;
        }

        // Admin has all permissions
        if ($this->role->isAdmin()) {
            return true;
        }

        return $this->role->hasPermission($permissionSlug);
    }

    /**
     * Check if user can access a specific page
     */
    public function canAccessPage($pageRoute)
    {
        if (!$this->role) {
            return false;
        }

        // Admin can access all pages
        if ($this->role->isAdmin()) {
            return true;
        }

        $pagePermission = PagePermission::where('page_route', $pageRoute)->first();
        if (!$pagePermission) {
            // If page permission doesn't exist, allow access by default
            return true;
        }

        return $pagePermission->roles()->where('role_id', $this->role_id)->exists();
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role && $this->role->isAdmin();
    }

    /**
     * Check if user is moderator
     */
    public function isModerator()
    {
        return $this->role && $this->role->slug === 'moderator';
    }

    /**
     * Check if user can edit a post
     */
    public function canEditPost($post)
    {
        // Admin can edit everything
        if ($this->isAdmin()) {
            return true;
        }

        // User can edit only their own posts
        if ($this->role && $this->role->slug === 'user') {
            return $post->created_user_id === $this->id;
        }

        // Moderator can edit everyone's posts by default
        if ($this->isModerator()) {
            return true;
        }

        // For other roles, check if they have the permission
        return $this->hasPermission('edit_any_post');
    }

    /**
     * Check if user can delete a post
     */
    public function canDeletePost($post)
    {
        // Admin can delete everything
        if ($this->isAdmin()) {
            return true;
        }

        // Owner can delete their own posts
        if ($post->created_user_id === $this->id) {
            return true;
        }

        // Check if user has delete permission
        return $this->hasPermission('delete_any_post');
    }
}
