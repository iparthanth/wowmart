<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One users table for every actor in the marketplace.
 *
 * A seller, a delivery person and an admin are all people who sign in, so they
 * share one table and one auth guard; `user_type` tells them apart. Keeping it a
 * string (backed by a PHP enum) rather than a DB enum means a new role never
 * needs an ALTER TABLE.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_type', 20)->default('customer')->index()->after('id');
            $table->string('phone', 32)->nullable()->after('email');
            $table->string('country_code', 8)->nullable()->after('phone');
            $table->boolean('banned')->default(false)->after('password');
            $table->string('email')->nullable()->change();

            $table->unique('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->dropColumn(['user_type', 'phone', 'country_code', 'banned']);
        });
    }
};
