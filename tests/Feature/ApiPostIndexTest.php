<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiPostIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_lists_only_published_active_posts(): void
    {
        Post::create([
            'slug' => 'visible-post',
            'title' => ['en' => 'Visible'],
            'content' => ['en' => 'Body'],
            'is_active' => true,
            'published_at' => now()->subDay(),
            'likes' => 3,
            'view_count' => 9,
        ]);

        Post::create([
            'slug' => 'draft-post',
            'title' => ['en' => 'Draft'],
            'content' => ['en' => 'Hidden'],
            'is_active' => false,
            'published_at' => now()->subDay(),
        ]);

        Post::create([
            'slug' => 'future-post',
            'title' => ['en' => 'Future'],
            'content' => ['en' => 'Later'],
            'is_active' => true,
            'published_at' => now()->addDay(),
        ]);

        $response = $this->getJson('/api/posts');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', 'visible-post')
            ->assertJsonPath('data.0.title.en', 'Visible')
            ->assertJsonPath('data.0.likes', 3)
            ->assertJsonPath('data.0.views', 9)
            ->assertJsonPath('data.0.created_user_name', null)
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'slug',
                    'title',
                    'content',
                    'created_user_name',
                    'likes',
                    'rate',
                    'views',
                    'category_name',
                    'thumbnail',
                    'published_at',
                ]],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'from',
                    'to',
                ],
            ]);
    }
}
