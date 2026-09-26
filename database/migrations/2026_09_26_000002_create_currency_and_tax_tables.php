<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('symbol', 8);
            $table->string('code', 3);
            $table->decimal('exchange_rate', 12, 6)->default(1);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique('code');
        });

        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('product_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_id')->constrained()->cascadeOnDelete();
            $table->decimal('tax', 12, 2);
            $table->string('tax_type', 10)->default('percent');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_taxes');
        Schema::dropIfExists('taxes');
        Schema::dropIfExists('currencies');
    }
};
