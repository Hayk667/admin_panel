<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if English already exists
        $english = Language::where('code', 'en')->first();
        
        if (!$english) {
            Language::create([
                'code' => 'en',
                'name' => 'English',
                'is_default' => true,
                'is_active' => true,
            ]);
        } else {
            // Ensure English is set as default
            $english->update([
                'is_default' => true,
                'is_active' => true,
            ]);
        }
    }
}

