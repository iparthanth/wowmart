<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Order history must outlive any attempt to delete its customer.
 *
 * CustomerService::delete already refuses a customer with orders; this makes the
 * database refuse too, so a stray DELETE (tinker, a script, the storefront) can
 * no longer wipe a customer's orders through the old cascade. On SQLite Laravel
 * rebuilds the table to change the key, which it does for us.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
