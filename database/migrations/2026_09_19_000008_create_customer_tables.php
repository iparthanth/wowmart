<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tables written by the storefront and read by the admin panel.
 *
 * They live in the admin app's migrations because one application must own the
 * shared schema. The storefront reads and writes these rows but never alters
 * their structure.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone', 32);
            $table->text('address');
            $table->string('city', 120);
            $table->string('postal_code', 20)->nullable();
            $table->boolean('set_default')->default(false);
            $table->timestamps();
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('temp_user_id', 64)->nullable()->index(); // guest carts
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('variation')->default('');
            $table->decimal('price', 20, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
        });

        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->boolean('status')->default(true);   // admin can hide a review
            $table->timestamps();
            $table->unique(['product_id', 'user_id']);
        });
    }

    public function down(): void
    {
        foreach (['reviews', 'wishlists', 'carts', 'addresses'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
