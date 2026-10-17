<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Products and everything that hangs off one.
 *
 * Column groups follow the seven tabs of the captured "Add New Product" form
 * (docs/reference/product-form.md), so a reviewer can read the form contract and
 * this table side by side. Field names match the form's input names, which keeps
 * the request-to-column mapping trivially auditable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Ownership: admin products are "in house"; seller products belong to a shop owner.
            $table->string('added_by', 16)->default('admin')->index();   // admin | seller
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // -- General ---------------------------------------------------------
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete(); // the MAIN category
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('unit', 32)->nullable();
            $table->decimal('weight', 10, 3)->default(0);
            $table->unsignedInteger('min_qty')->default(1);
            $table->text('tags')->nullable();              // comma-separated, as the tag input submits
            $table->string('barcode', 64)->nullable();
            $table->text('quick_overview')->nullable();
            $table->longText('description')->nullable();
            $table->boolean('refundable')->default(false);
            $table->foreignId('refund_note_id')->nullable()->constrained('notes')->nullOnDelete();
            $table->boolean('featured')->default(false);
            $table->boolean('todays_deal')->default(false);

            // -- Files & Media ---------------------------------------------------
            // Gallery images live in product_upload (below); only single refs sit here.
            $table->foreignId('thumbnail_img')->nullable()->constrained('uploads')->nullOnDelete();
            $table->string('video_provider', 16)->nullable();   // youtube | vimeo
            $table->string('video_link')->nullable();
            $table->foreignId('pdf')->nullable()->constrained('uploads')->nullOnDelete();

            // -- Price & Stock ---------------------------------------------------
            $table->decimal('unit_price', 20, 2)->default(0);
            $table->decimal('discount', 20, 2)->default(0);
            $table->string('discount_type', 8)->default('amount');   // amount | percent
            $table->timestamp('discount_start_date')->nullable();
            $table->timestamp('discount_end_date')->nullable();
            // Signed on purpose: a concurrent sale can briefly overshoot, and an
            // unsigned column would throw instead of letting the app detect it.
            $table->integer('current_stock')->default(0);
            $table->string('sku', 64)->nullable()->index();
            $table->unsignedInteger('low_stock_quantity')->default(1);
            $table->string('stock_visibility_state', 8)->default('quantity'); // quantity | text | hide
            $table->boolean('variant_product')->default(false);
            $table->json('colors')->nullable();
            $table->json('choice_attributes')->nullable();   // not 'attributes': that name collides with Eloquent's internal $attributes
            $table->json('choice_options')->nullable();
            $table->string('external_link')->nullable();
            $table->string('external_link_btn')->nullable();

            // -- SEO -------------------------------------------------------------
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->foreignId('meta_img')->nullable()->constrained('uploads')->nullOnDelete();

            // -- Shipping --------------------------------------------------------
            $table->boolean('cash_on_delivery')->default(true);
            $table->string('shipping_type', 16)->default('free');     // free | flat_rate
            $table->decimal('shipping_cost', 20, 2)->default(0);
            $table->boolean('is_quantity_multiplied')->default(false);
            $table->unsignedSmallInteger('est_shipping_days')->nullable();

            // -- Warranty --------------------------------------------------------
            $table->boolean('has_warranty')->default(false);
            $table->foreignId('warranty_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('warranty_note_id')->nullable()->constrained('notes')->nullOnDelete();

            // -- Frequently Bought -----------------------------------------------
            $table->string('frequently_bought_selection_type', 16)->default('product'); // product | category
            $table->foreignId('fq_bought_product_category_id')->nullable()->constrained('categories')->nullOnDelete();

            // -- Lifecycle & merchandising ---------------------------------------
            $table->boolean('published')->default(true);
            $table->boolean('approved')->default(true);            // seller products wait for approval
            $table->boolean('wholesale_product')->default(false);
            $table->boolean('digital')->default(false);
            $table->unsignedInteger('num_of_sale')->default(0);
            $table->decimal('rating', 3, 2)->default(0);

            $table->timestamps();

            // Mirrors the list screen's filters and sorts.
            $table->index(['published', 'approved']);
            $table->index('num_of_sale');
            $table->index('unit_price');
        });

        // category_ids[] - every category a product appears in.
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->unique(['product_id', 'category_id']);
        });

        // photos - the ordered gallery. The form posts upload ids as CSV; they are
        // normalised here so an image can be joined, counted and FK-checked, and so
        // "which products use this file" is a query instead of a LIKE scan.
        Schema::create('product_upload', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('upload_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unique(['product_id', 'upload_id']);
        });

        // One row per sellable variant. A simple product has exactly one row with
        // an empty `variant`, so stock is always read from one place.
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('variant')->default('');
            $table->string('sku', 64)->nullable();
            $table->decimal('price', 20, 2)->default(0);
            $table->integer('qty')->default(0);
            $table->foreignId('image')->nullable()->constrained('uploads')->nullOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'variant']);
        });

    }

    public function down(): void
    {
        foreach (['product_stocks', 'product_upload', 'product_categories', 'products'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
