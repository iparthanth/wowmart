<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Static pages the storefront renders at /page/{slug}: about, terms, privacy
 * and anything the admin writes. `type` marks the standard three so the
 * storefront can pin them into its footer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('custom');
            $table->string('title', 120);
            $table->string('slug', 140);
            $table->text('content')->nullable();
            $table->string('meta_title', 180)->nullable();
            $table->string('meta_description', 300)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
