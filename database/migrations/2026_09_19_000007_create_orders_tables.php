<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();          // human reference, e.g. 20260721-14411730
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->nullable()->constrained('users')->nullOnDelete();

            // Snapshotted, not referenced: editing an address book entry later must
            // never rewrite where a past order was actually sent.
            $table->json('shipping_address');

            // Exactly the six states the reference panel filters by. No "processing".
            $table->string('delivery_status', 16)->default('pending')->index();
            $table->string('payment_type', 32)->default('cash_on_delivery');
            $table->string('payment_status', 8)->default('unpaid')->index();

            $table->decimal('grand_total', 20, 2)->default(0);
            $table->decimal('coupon_discount', 20, 2)->default(0);
            $table->string('coupon_code', 64)->nullable();

            $table->timestamps();
            $table->index(['delivery_status', 'id']);
        });

        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            $table->string('variation')->default('');
            $table->decimal('price', 20, 2);
            $table->decimal('tax', 20, 2)->default(0);
            $table->decimal('shipping_cost', 20, 2)->default(0);
            $table->unsignedInteger('quantity');

            // Per LINE, not per order: in a multi-vendor order each seller's items
            // ship separately, which is why the reference detail page shows
            // "Delivery Type" on every line item.
            $table->string('shipping_type', 24)->default('home_delivery');
            $table->string('delivery_status', 16)->default('pending');
            $table->string('payment_status', 8)->default('unpaid');

            $table->timestamps();
            $table->index(['order_id', 'seller_id']);
        });
    }

    public function down(): void
    {
        foreach (['order_details', 'orders'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
