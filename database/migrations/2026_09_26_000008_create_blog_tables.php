<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The blog: categories group the posts; each post carries its own banner,
 * short description for lists, full body and SEO meta.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('slug', 80);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique('slug');
        });

        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_category_id')->constrained('blog_categories')->cascadeOnDelete();
            $table->string('title', 160);
            $table->string('slug', 180);
            $table->string('short_description', 300)->nullable();
            $table->text('description')->nullable();
            $table->string('banner')->nullable();
            $table->string('meta_title', 180)->nullable();
            $table->string('meta_description', 300)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blogs');
        Schema::dropIfExists('blog_categories');
    }
};
