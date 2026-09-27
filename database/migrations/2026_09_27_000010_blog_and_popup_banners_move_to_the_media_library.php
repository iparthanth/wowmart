<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blog post and pop-up banners move to the media library model: like products,
 * brands, categories and flash deals they now reference an uploaded image by
 * id, so forms pick from the library instead of pasting a URL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table): void {
            $table->dropColumn('banner');
        });

        Schema::table('blogs', function (Blueprint $table): void {
            $table->foreignId('banner')->nullable()->constrained('uploads')->nullOnDelete();
        });

        Schema::table('dynamic_popups', function (Blueprint $table): void {
            $table->dropColumn('banner');
        });

        Schema::table('dynamic_popups', function (Blueprint $table): void {
            $table->foreignId('banner')->nullable()->constrained('uploads')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('banner');
            $table->string('banner')->nullable();
        });

        Schema::table('dynamic_popups', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('banner');
            $table->string('banner')->nullable();
        });
    }
};
