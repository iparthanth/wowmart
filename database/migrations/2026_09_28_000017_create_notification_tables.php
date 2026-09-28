<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The notification system:
 * - notification_types: the icons and default texts shown on the storefront
 * - custom_notifications (+ user pivot): staff-sent announcements, listed in history
 * - bulk_sms_messages (+ recipients): SMS blasts to mobile users
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
            $table->foreignId('image')->nullable()->constrained('uploads')->nullOnDelete();
            $table->string('default_text', 80);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('custom_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_type_id')->constrained()->cascadeOnDelete();
            $table->string('content', 80);
            $table->string('link')->nullable();
            $table->timestamps();
        });

        Schema::create('custom_notification_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_notification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('bulk_sms_messages', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->string('template_id', 60)->nullable();
            $table->unsignedInteger('recipient_count')->default(0);
            $table->timestamps();
        });

        Schema::create('bulk_sms_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulk_sms_message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone', 32);
            // sent | failed
            $table->string('status', 10)->default('sent');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['bulk_sms_recipients', 'bulk_sms_messages', 'custom_notification_user', 'custom_notifications', 'notification_types'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
