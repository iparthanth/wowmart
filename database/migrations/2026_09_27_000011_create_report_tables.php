<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The remaining report sources:
 * - search_logs: what visitors typed into the storefront search box (User Searches)
 * - commission_logs: one row per paid seller order (Commission History, Earning Report)
 * - wallet_recharges: customers' offline wallet top-ups awaiting approval (Wallet Recharge History)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->string('query', 180);
            $table->unsignedInteger('results')->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->index('query');
        });

        Schema::create('commission_logs', function (Blueprint $table) {
            $table->id();
            // One row per seller inside a paid order: a multi-vendor order pays
            // each shop separately, so (order, seller) is the unit.
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // Snapshot: the row keeps the reference even if the order's code changes.
            $table->string('order_code', 32);
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('admin_commission', 20, 2)->default(0);
            $table->decimal('seller_earning', 20, 2)->default(0);
            $table->timestamps();
            $table->unique(['order_id', 'seller_id']);
        });

        Schema::create('wallet_recharges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 20, 2);
            $table->string('payment_method', 32);
            $table->string('transaction_id', 64)->nullable();
            // pending | approved | rejected
            $table->string('approval', 16)->default('pending')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['wallet_recharges', 'commission_logs', 'search_logs'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
