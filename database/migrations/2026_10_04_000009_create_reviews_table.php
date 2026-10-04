<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->unsignedTinyInteger('quality_rating');
            $table->unsignedTinyInteger('transparency_rating');
            $table->unsignedTinyInteger('value_rating');
            $table->string('title');
            $table->text('body');
            $table->boolean('verified_purchase')->default(false);
            $table->unsignedInteger('helpful_count')->default(0);
            // pending | published | rejected | flagged
            $table->string('status')->default('pending')->index();
            // Public answer from the producer
            $table->text('reply_body')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
        });

        // Moderation history: submitted, published, flagged, rejected…
        Schema::create('review_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('author');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_events');
        Schema::dropIfExists('reviews');
    }
};
