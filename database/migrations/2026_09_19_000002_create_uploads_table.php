<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Uploaded Files" - the shared media library.
 *
 * Every image in the system is uploaded here once and referenced by id from
 * products, categories, brands and banners. Nothing else ever stores a file.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->string('file_original_name');
            $table->string('file_name');                 // stored path, relative to public/
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('extension', 16)->nullable();
            $table->string('type', 24)->default('image'); // image | video | document | archive
            $table->timestamps();

            // The screen sorts by newest/oldest/smallest/largest and searches by name.
            $table->index('file_size');
            $table->index('file_original_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
