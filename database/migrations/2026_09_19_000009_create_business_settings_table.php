<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Key/value site configuration.
 *
 * Captured from the reference: many unrelated settings screens (Select
 * Homepage, header, footer, appearance...) all post to one update endpoint,
 * naming the keys they write. That only works because settings are rows, not
 * columns - adding a setting is an INSERT, never a migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type', 100);          // the setting key, e.g. homepage_select
            $table->longText('value')->nullable();
            $table->string('lang', 8)->default('');   // '' = every language. Not NULL: MySQL lets NULLs repeat in a unique index
            $table->timestamps();

            $table->unique(['type', 'lang']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
