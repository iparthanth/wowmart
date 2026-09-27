<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Database\Seeder;

/** A small deterministic blog: two categories, three posts. */
class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $news = BlogCategory::firstOrCreate(['slug' => 'news'], ['name' => 'News', 'slug' => 'news', 'status' => true]);
        $guides = BlogCategory::firstOrCreate(['slug' => 'guides'], ['name' => 'Buying Guides', 'slug' => 'guides', 'status' => true]);

        Blog::firstOrCreate(['slug' => 'wowmart-opens-new-warehouse'], [
            'blog_category_id' => $news->id,
            'title' => 'WowMart opens a new warehouse',
            'slug' => 'wowmart-opens-new-warehouse',
            'short_description' => 'Faster deliveries for Dhaka and Chattogram start this month.',
            'description' => 'The new warehouse doubles our storage and cuts delivery times by a day for most addresses.',
            'status' => true,
        ]);

        Blog::firstOrCreate(['slug' => 'how-to-choose-a-rice-cooker'], [
            'blog_category_id' => $guides->id,
            'title' => 'How to choose a rice cooker',
            'slug' => 'how-to-choose-a-rice-cooker',
            'short_description' => 'Capacity, keep-warm and what the wattage really means.',
            'description' => 'A five-minute guide to picking the right rice cooker for your family size.',
            'status' => true,
        ]);

        Blog::firstOrCreate(['slug' => 'grocery-shopping-on-a-budget'], [
            'blog_category_id' => $guides->id,
            'title' => 'Grocery shopping on a budget',
            'slug' => 'grocery-shopping-on-a-budget',
            'short_description' => 'Seven habits that cut the monthly bill without cutting quality.',
            'description' => 'Plan around staples, buy wholesale where it counts, and watch the unit price.',
            'status' => false, // a draft, so the lists have something to filter
        ]);
    }
}
