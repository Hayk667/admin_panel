<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('published_at')->nullable();
            $table->string('image')->nullable();
            $table->string('thumbnail')->nullable();
            // Multilingual title stored as JSON: {"en": "Post Title", "ru": "Заголовок поста"}
            $table->json('title')->nullable();
            // Multilingual content stored as JSON: {"en": "Post content...", "ru": "Содержание поста..."}
            $table->json('content')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};

