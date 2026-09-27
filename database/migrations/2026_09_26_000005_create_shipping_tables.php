<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The shipping area: where the panel can deliver, what each trip costs, and
 * how carrier billing is set up.
 *
 * Hierarchy: countries hold states, states hold cities (each with a shipping
 * cost for area-wise shipping). Zones group whole countries for carrier
 * billing: a range prices each zone once, and every country in the zone is
 * billed with that price.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('code', 4);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique('code');
        });

        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['country_id', 'name']);
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('state_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->decimal('cost', 12, 2)->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['state_id', 'name']);
        });

        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('zone_country', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['zone_id', 'country_id']);
        });

        Schema::create('carriers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('logo')->nullable();
            $table->string('transit_time', 60)->nullable();
            $table->boolean('free_shipping')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('carrier_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->constrained()->cascadeOnDelete();
            $table->string('billing_type', 10)->default('weight');
            $table->decimal('lower', 12, 2)->default(0);
            $table->decimal('upper', 12, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('carrier_range_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_range_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 12, 2);
            $table->timestamps();

            $table->unique(['carrier_range_id', 'zone_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrier_range_prices');
        Schema::dropIfExists('carrier_ranges');
        Schema::dropIfExists('carriers');
        Schema::dropIfExists('zone_country');
        Schema::dropIfExists('zones');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
    }
};
