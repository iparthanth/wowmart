<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

/** The languages the panel ships with; admins add more. */
class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'English', 'code' => 'en', 'rtl' => false, 'status' => true],
            ['name' => 'বাংলা', 'code' => 'bn', 'rtl' => false, 'status' => true],
            ['name' => 'العربية', 'code' => 'ar', 'rtl' => true, 'status' => false],
            ['name' => 'हिन्दी', 'code' => 'hi', 'rtl' => false, 'status' => false],
        ] as $language) {
            Language::create($language);
        }
    }
}
