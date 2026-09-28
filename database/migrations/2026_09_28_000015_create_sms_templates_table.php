<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The OTP system: configurable one-time passwords plus the SMS text that
 * carries them (and other messages) through the panel's SMS gateway.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique(); // e.g. otp_login, order_placed
            $table->string('name', 80);
            $table->text('body'); // supports {placeholders}
            $table->string('template_id', 60)->nullable(); // provider/DLT reference
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_templates');
    }
};
