<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title ?? [],
            'content' => $this->content ?? [],
            'created_user_name' => $this->createdUser?->name,
            'likes' => (int) $this->likes,
            'rate' => $this->rate !== null ? (float) $this->rate : null,
            'views' => (int) $this->view_count,
            'category_name' => $this->category?->name ?? [],
            'thumbnail' => $this->thumbnail ? url('storage/'.$this->thumbnail) : null,
            'published_at' => $this->published_at?->format('Y-m-d, H:i'),
        ];
    }
}
