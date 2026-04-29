<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ----- Placements (the cross-mode foundation) -----
        // host_school_id intentionally points at `schools` — this is the only
        // touchpoint between IPG and School modes per IPG_MODE_CHECKLIST §0.
        Schema::create('placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_id')->constrained('trainees')->cascadeOnDelete();
            $table->foreignId('host_school_id')->constrained('schools')->restrictOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('supervisor_pensyarah_id')->nullable()->constrained('pensyarahs')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->json('subjects')->nullable();    // ["Matematik","Bahasa Melayu"]
            $table->json('levels')->nullable();      // ["Tahun 4","Tahun 5"]
            $table->string('status')->default('scheduled'); // scheduled, active, completed, cancelled
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['host_school_id', 'start_date', 'end_date']);
            $table->index(['supervisor_pensyarah_id', 'status']);
            $table->index(['trainee_id', 'semester_id']);
        });

        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('placement_id')->constrained('placements')->cascadeOnDelete();
            $table->foreignId('evaluated_by_pensyarah_id')->nullable()->constrained('pensyarahs')->nullOnDelete();
            $table->date('observed_at');
            $table->string('lesson_topic');
            $table->text('notes')->nullable();
            $table->unsignedTinyInteger('rubric_score')->nullable(); // 0..100
            $table->timestamps();
            $table->index(['placement_id', 'observed_at']);
        });

        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('placement_id')->constrained('placements')->cascadeOnDelete()->unique(); // one final eval per placement
            $table->foreignId('evaluated_by_pensyarah_id')->nullable()->constrained('pensyarahs')->nullOnDelete();
            $table->unsignedTinyInteger('score')->nullable(); // 0..100
            $table->string('grade_letter', 4)->nullable();
            $table->text('comments')->nullable();
            $table->date('evaluated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('logbook_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('placement_id')->constrained('placements')->cascadeOnDelete();
            $table->foreignId('reviewed_by_pensyarah_id')->nullable()->constrained('pensyarahs')->nullOnDelete();
            $table->unsignedSmallInteger('week_number');
            $table->text('reflection_text');
            $table->date('submitted_at')->nullable();
            $table->date('reviewed_at')->nullable();
            $table->text('review_comment')->nullable();
            $table->timestamps();
            $table->unique(['placement_id', 'week_number']);
        });

        Schema::create('placement_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('placement_id')->constrained('placements')->cascadeOnDelete();
            $table->string('kind')->default('placement_letter'); // placement_letter, evaluation_letter, ...
            $table->string('principal_name')->nullable();
            $table->date('sent_at')->nullable();
            $table->date('acknowledged_at')->nullable();
            $table->text('body')->nullable();
            $table->timestamps();
            $table->index(['placement_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('placement_letters');
        Schema::dropIfExists('logbook_entries');
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('observations');
        Schema::dropIfExists('placements');
    }
};
