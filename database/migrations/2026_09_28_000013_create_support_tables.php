<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The support section:
 * - support_tickets + replies: a customer's ticket thread with the panel
 * - contact_messages: what visitors send through the contact form
 * - product_queries: a question on a product that an admin answers
 * - product_conversations + messages: a buyer-seller chat about one product
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16)->unique(); // human reference, e.g. TKT-000007
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject', 180);
            // open | closed
            $table->string('status', 12)->default('open')->index();
            $table->timestamps();
        });

        Schema::create('support_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email');
            $table->string('phone', 40)->nullable();
            $table->text('query');
            $table->text('reply')->nullable();
            // open | answered
            $table->string('status', 12)->default('open')->index();
            $table->timestamps();
        });

        Schema::create('product_queries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->text('reply')->nullable();
            // open | answered
            $table->string('status', 12)->default('open')->index();
            $table->timestamps();
        });

        Schema::create('product_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 180);
            // open | closed
            $table->string('status', 12)->default('open')->index();
            $table->timestamps();
        });

        Schema::create('product_conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_conversation_id')->constrained('product_conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        foreach (['product_conversation_messages', 'product_conversations', 'product_queries', 'contact_messages', 'support_ticket_replies', 'support_tickets'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
