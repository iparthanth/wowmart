<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The affiliate system:
 * - affiliates: customers who promote products, one row per user, own referral code
 * - affiliate_referrals: who was brought in by whom
 * - affiliate_withdrawals: requests to pay an affiliate's balance out
 * - affiliate_logs: the money trail (commission earned, withdrawals paid)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('code', 16)->unique(); // referral code, e.g. AFF-4KQ7
            // pending | approved | rejected
            $table->string('status', 12)->default('pending')->index();
            $table->decimal('balance', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('affiliate_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referred_user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('affiliate_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            // pending | approved | rejected
            $table->string('status', 12)->default('pending')->index();
            $table->text('note')->nullable(); // why a request was rejected
            $table->timestamps();
        });

        Schema::create('affiliate_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referred_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            // registration | purchase | withdrawal
            $table->string('type', 20);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('description', 180);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['affiliate_logs', 'affiliate_withdrawals', 'affiliate_referrals', 'affiliates'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
