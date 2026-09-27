<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Delivery boys work on orders: the admin assigns a boy, and a boy asks to
 * cancel an order they cannot complete - the admin approves or declines that
 * request from the cancel-requests list.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('delivery_boy_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancel_requested_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_boy_id');
            $table->dropColumn('cancel_requested_at');
        });
    }
};
