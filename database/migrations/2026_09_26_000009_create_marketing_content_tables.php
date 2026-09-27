<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Marketing: pop-ups and alerts the storefront shows, the email templates the
 * queued notifications render, and the newsletter list.
 *
 * - dynamic_popups: one pop-up window (title/description, banner, shown on a
 *   department page or globally, only once per session).
 * - custom_alerts: the sticky header notice with optional styling.
 * - email_templates: what order/review/refund mails are built from; exactly
 *   one template per (receiver, identifier) is the default.
 * - subscribers: the footer opt-ins (email unique).
 * - newsletters: a sent log (subject/body to a segment); sending is queued.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dynamic_popups', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->text('description')->nullable();
            $table->string('banner')->nullable();
            $table->string('link')->nullable();
            $table->string('page', 40)->default('all');
            $table->boolean('once_per_session')->default(true);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('custom_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('title', 160);
            $table->text('description')->nullable();
            $table->string('style', 20)->default('info');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('receiver', 20);
            $table->string('identifier', 60);
            $table->string('subject', 180);
            $table->text('body');
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['receiver', 'identifier']);
        });

        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique('email');
        });

        Schema::create('newsletters', function (Blueprint $table) {
            $table->id();
            $table->string('subject', 180);
            $table->text('body');
            $table->string('segment', 20)->default('all');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletters');
        Schema::dropIfExists('subscribers');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('custom_alerts');
        Schema::dropIfExists('dynamic_popups');
    }
};
