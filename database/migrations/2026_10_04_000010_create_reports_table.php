<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 20)->unique();
            // greenwashing | dubious_certification | traceability_error | misleading_footprint | health_quality | other
            $table->string('type')->index();
            // product | actor | certification (morph map in AppServiceProvider)
            $table->morphs('reportable');
            $table->string('title');
            $table->text('description');
            // pending | in_review | confirmed | rejected | resolved
            $table->string('status')->default('pending')->index();
            // low | medium | high | critical
            $table->string('priority')->default('medium')->index();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution')->nullable();
            $table->timestamps();
        });

        Schema::create('report_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            // file | link
            $table->string('kind');
            $table->string('name');
            $table->string('size')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();
        });

        // Conversation between the reporter and the moderators
        Schema::create('report_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // reporter | moderator
            $table->string('role');
            $table->text('body');
            $table->json('attachments');
            $table->timestamps();
        });

        // Internal notes, visible to moderators only
        Schema::create('report_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('report_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('author');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_events');
        Schema::dropIfExists('report_notes');
        Schema::dropIfExists('report_messages');
        Schema::dropIfExists('report_evidence');
        Schema::dropIfExists('reports');
    }
};
