<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('co2_per_kg', 6, 2);
            $table->decimal('water_per_kg', 10, 2);
            $table->decimal('distance_km', 8, 2);
            // compostable | recyclable | mixed | plastic
            $table->string('packaging');
            $table->boolean('seasonal')->default(false);
            // Derived from the indicators above by App\Support\EcoScore (see Impact::booted()).
            $table->unsignedTinyInteger('eco_points');
            $table->char('eco_score', 1)->index();
            // Share of emissions (%) per stage: Production, Transformation, Transport, Emballage
            $table->json('breakdown');
            $table->timestamps();
        });

        Schema::create('emission_factors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->index();
            $table->string('unit');
            $table->decimal('value', 10, 3);
            $table->string('source');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emission_factors');
        Schema::dropIfExists('impacts');
    }
};
