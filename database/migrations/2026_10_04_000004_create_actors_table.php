<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actors', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            // producer | processor | distributor
            $table->string('type')->index();
            $table->string('city');
            $table->string('region');
            $table->text('description');
            $table->unsignedSmallInteger('founded_year')->nullable();
            $table->boolean('verified')->default(false);
            $table->string('email');
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        Schema::create('actor_certification', function (Blueprint $table) {
            $table->foreignId('actor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
            $table->primary(['actor_id', 'certification_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actor_certification');
        Schema::dropIfExists('actors');
    }
};
