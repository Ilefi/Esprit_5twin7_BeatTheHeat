<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('impacts', function (Blueprint $table) {
            $table->foreignId('emission_factor_id')
                ->nullable()
                ->after('product_id')
                ->constrained('emission_factors')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('impacts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('emission_factor_id');
        });
    }
};
