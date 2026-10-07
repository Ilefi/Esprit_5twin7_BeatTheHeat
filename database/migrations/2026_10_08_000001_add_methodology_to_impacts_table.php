<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('impacts', function (Blueprint $table) {
            // lca | measured | estimated | declared (labels in App\Support\EcoScore::METHODOLOGIES)
            $table->string('methodology')->default('declared')->after('seasonal');
            $table->string('source')->nullable()->after('methodology');
        });
    }

    public function down(): void
    {
        Schema::table('impacts', function (Blueprint $table) {
            $table->dropColumn(['methodology', 'source']);
        });
    }
};
