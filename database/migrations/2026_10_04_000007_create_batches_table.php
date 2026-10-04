<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('quantity');
            // in_production | in_transit | delivered
            $table->string('status')->default('in_production')->index();
            $table->date('production_date');
            $table->timestamps();
        });

        Schema::create('batch_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('position');
            // production | processing | distribution | consumer
            $table->string('stage');
            $table->string('title');
            $table->string('location');
            $table->dateTime('date');
            $table->text('action');
            $table->json('documents');
            $table->unsignedInteger('distance_km')->default(0);
            $table->boolean('verified')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_steps');
        Schema::dropIfExists('batches');
    }
};
