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
        Schema::create('page_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('page_route')->unique(); // e.g., 'posts.index', 'categories.create'
            $table->string('page_name'); // Display name
            $table->timestamps();
        });
        
        Schema::create('page_permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_permission_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['page_permission_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_permission_role');
        Schema::dropIfExists('page_permissions');
    }
};

