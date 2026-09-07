<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $defaultLang = DB::table('languages')
            ->where('is_default', true)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->value('code') ?? 'en';

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE tags MODIFY name TEXT NULL');
        }

        $tags = DB::table('tags')->get();
        foreach ($tags as $tag) {
            $decoded = json_decode($tag->name, true);
            if (is_array($decoded)) {
                continue;
            }

            DB::table('tags')->where('id', $tag->id)->update([
                'name' => json_encode([$defaultLang => $tag->name], JSON_UNESCAPED_UNICODE),
            ]);
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE tags MODIFY name JSON NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE tags ALTER COLUMN name TYPE JSON USING name::json');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tags = DB::table('tags')->get();
        foreach ($tags as $tag) {
            $decoded = json_decode($tag->name, true);
            $plain = is_array($decoded) ? (reset($decoded) ?: '') : $tag->name;

            DB::table('tags')->where('id', $tag->id)->update([
                'name' => $plain,
            ]);
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE tags MODIFY name VARCHAR(255) NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE tags ALTER COLUMN name TYPE VARCHAR(255)');
        }
    }
};
