<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->foreignId('category_id')->constrained();
            $table->foreignId('producer_id')->constrained('actors');
            $table->foreignId('processor_id')->nullable()->constrained('actors')->nullOnDelete();
            $table->string('region');
            $table->string('format');
            $table->decimal('price', 10, 2);
            $table->text('description');
            $table->text('composition')->nullable();
            $table->string('image')->nullable();
            // draft | pending | published
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });

        Schema::create('certification_product', function (Blueprint $table) {
            $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['certification_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certification_product');
        Schema::dropIfExists('products');
    }
};
