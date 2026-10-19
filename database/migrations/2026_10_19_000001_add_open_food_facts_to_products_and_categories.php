<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // EAN-8 / EAN-13 barcode, used to read nutrition data from Open Food Facts.
            $table->string('barcode', 14)->nullable()->unique()->after('slug');
        });

        Schema::table('categories', function (Blueprint $table) {
            // Open Food Facts taxonomy tag (e.g. en:honeys), used for the external benchmark.
            $table->string('off_tag')->nullable()->after('icon');
        });

        // Categories whose products can replace each other in recommendations (stored in both directions).
        Schema::create('category_substitutes', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('substitute_id')->constrained('categories')->cascadeOnDelete();
            $table->primary(['category_id', 'substitute_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_substitutes');

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('off_tag');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['barcode']);
            $table->dropColumn('barcode');
        });
    }
};
