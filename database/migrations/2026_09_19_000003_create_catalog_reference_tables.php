<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The lookup tables a product points at: brands, categories, colours,
 * attributes, warranties and reusable notes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('logo')->nullable()->constrained('uploads')->nullOnDelete();
            $table->boolean('top')->default(false);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->unsignedTinyInteger('level')->default(0);   // depth in the tree
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('order_level')->default(0);
            // Three distinct images, as on the reference Categories screen.
            $table->foreignId('banner')->nullable()->constrained('uploads')->nullOnDelete();
            $table->foreignId('icon')->nullable()->constrained('uploads')->nullOnDelete();
            $table->foreignId('cover_image')->nullable()->constrained('uploads')->nullOnDelete();
            $table->boolean('featured')->default(false);
            $table->boolean('digital')->default(false);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();

            $table->index(['parent_id', 'order_level']);
        });

        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 16);        // hex, e.g. #1F4B3F
            $table->timestamps();
        });

        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');            // e.g. Size, Length, Capacity
            $table->timestamps();
        });

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value');           // e.g. 80mm, 50m
            $table->timestamps();
        });

        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->string('text');            // e.g. "6 months replacement"
            $table->timestamps();
        });

        // "Notes" menu: reusable blocks of text (refund terms, warranty terms)
        // attached to many products instead of being retyped on each one.
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->string('type', 24);        // refund | warranty
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['notes', 'warranties', 'attribute_values', 'attributes', 'colors', 'categories', 'brands'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
