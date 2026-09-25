<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A seller's storefront. The seller themself is a users row with user_type = seller. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('logo')->nullable()->constrained('uploads')->nullOnDelete();
            $table->text('address')->nullable();
            $table->string('phone', 32)->nullable();
            // pending until an admin approves the verification form.
            $table->string('verification_status', 16)->default('pending')->index();
            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('num_of_reviews')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
