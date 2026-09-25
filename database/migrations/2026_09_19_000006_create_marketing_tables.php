<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Three coupon kinds, as captured from the reference create screen:
        //   product_base  - discount on chosen products     (details: product_ids)
        //   cart_base     - discount on the cart total      (details: min_buy, max_discount)
        //   welcome_base  - first order after sign-up       (details: validation_days, min_buy)
        // Type-specific rules live in `details` rather than in sparse columns
        // that are null for two kinds out of three.
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // who created it
            $table->string('type', 16)->index();
            $table->string('code', 64)->unique();
            $table->json('details')->nullable();
            $table->decimal('discount', 20, 2);
            $table->string('discount_type', 8)->default('amount');   // amount | percent
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->index(['coupon_id', 'user_id']);
        });

        // A flash deal is also a landing page of its own (slug + featured + banner),
        // not just a price rule - which is why the reference list shows "Page Link".
        Schema::create('flash_deals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('banner')->nullable()->constrained('uploads')->nullOnDelete();
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->boolean('status')->default(false);
            $table->boolean('featured')->default(false);
            $table->string('background_color', 16)->nullable();
            $table->string('text_color', 16)->nullable();
            $table->timestamps();
        });

        Schema::create('flash_deal_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flash_deal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('discount', 20, 2)->default(0);
            $table->string('discount_type', 8)->default('amount');
            $table->unique(['flash_deal_id', 'product_id']);
        });
    }

    public function down(): void
    {
        foreach (['flash_deal_products', 'flash_deals', 'coupon_usages', 'coupons'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
