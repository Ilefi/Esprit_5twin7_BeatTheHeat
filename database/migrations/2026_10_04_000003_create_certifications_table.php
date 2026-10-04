<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('short_name', 30);
            // bio | local | fair | origin | no_pesticide | reasoned
            $table->string('type');
            $table->string('issuer');
            $table->text('description');
            $table->json('criteria');
            $table->json('guarantees');
            $table->json('limits');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};
