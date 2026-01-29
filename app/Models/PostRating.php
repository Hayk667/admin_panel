<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'ip_address',
        'user_agent',
        'rating',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * Get the post that was rated
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user who rated (if authenticated)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
