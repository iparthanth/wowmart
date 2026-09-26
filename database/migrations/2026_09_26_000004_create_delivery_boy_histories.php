<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_boy_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_boy_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('note', 200)->nullable();
            $table->timestamp('paid_at');
            $table->timestamps();
        });

        Schema::create('delivery_boy_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_boy_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('note', 200)->nullable();
            $table->timestamp('collected_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_boy_collections');
        Schema::dropIfExists('delivery_boy_payments');
    }
};
