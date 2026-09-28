<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The payment gateway system:
 * - manual_payment_methods: offline channels (bank, bKash...) shown at checkout
 * - offline_package_payments: proof-of-payment for customer/seller packages
 * - wallet_recharges + upload_id: the receipt photo for offline top-ups
 * - users + wallet_balance: the credited balance once a top-up is approved
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->foreignId('logo')->nullable()->constrained('uploads')->nullOnDelete();
            $table->text('instructions')->nullable(); // shown to the buyer at checkout
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('offline_package_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // customer | seller
            $table->string('package_type', 12)->index();
            $table->string('package_name', 80);
            $table->decimal('amount', 20, 2);
            $table->string('payment_method', 40);
            $table->string('transaction_id', 64)->nullable();
            $table->foreignId('upload_id')->nullable()->constrained('uploads')->nullOnDelete();
            // pending | approved | rejected
            $table->string('approval', 16)->default('pending')->index();
            $table->timestamps();
        });

        Schema::table('wallet_recharges', function (Blueprint $table) {
            $table->foreignId('upload_id')->nullable()->constrained('uploads')->nullOnDelete()->after('approval');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->decimal('wallet_balance', 20, 2)->default(0)->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('wallet_balance'));
        Schema::table('wallet_recharges', fn (Blueprint $table) => $table->dropConstrainedForeignId('upload_id'));
        Schema::dropIfExists('offline_package_payments');
        Schema::dropIfExists('manual_payment_methods');
    }
};
