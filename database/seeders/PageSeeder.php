<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/** The three standard storefront pages, seeded with placeholder copy. */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        Page::firstOrCreate(['slug' => 'about'], [
            'type' => 'about', 'title' => 'About Us', 'slug' => 'about', 'status' => true,
            'content' => 'Tell customers who WowMart is, what it sells and how it stands behind it.',
            'meta_title' => 'About Us',
        ]);

        Page::firstOrCreate(['slug' => 'terms'], [
            'type' => 'terms', 'title' => 'Terms & Conditions', 'slug' => 'terms', 'status' => true,
            'content' => 'The terms every order on WowMart agrees to.',
            'meta_title' => 'Terms & Conditions',
        ]);

        Page::firstOrCreate(['slug' => 'privacy-policy'], [
            'type' => 'privacy', 'title' => 'Privacy Policy', 'slug' => 'privacy-policy', 'status' => true,
            'content' => 'How WowMart collects, uses and protects customer data.',
            'meta_title' => 'Privacy Policy',
        ]);
    }
}
