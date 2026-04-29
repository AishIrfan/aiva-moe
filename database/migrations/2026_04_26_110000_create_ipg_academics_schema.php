<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ----- Courses (campus-scoped) -----
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->string('code');
            $table->string('title');
            $table->unsignedTinyInteger('credit_hours')->default(3);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->unique(['campus_id', 'code']);
        });

        // ----- Transcript entries -----
        // One row = one (trainee × semester × course) result. Aggregating these gives
        // semester GPA and CGPA without a separate denormalized table.
        Schema::create('transcript_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_id')->constrained('trainees')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->restrictOnDelete();
            $table->string('grade_letter', 4);
            $table->decimal('grade_point', 3, 2);
            $table->timestamps();
            $table->unique(['trainee_id', 'semester_id', 'course_id'], 'transcript_entries_natural');
            $table->index(['trainee_id', 'semester_id']);
        });

        // ----- Co-curriculum activities + participations -----
        Schema::create('cocurricular_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('category')->nullable(); // sukan, kelab, persatuan, beruniform
            $table->unsignedTinyInteger('max_units')->default(3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['campus_id', 'code']);
        });

        Schema::create('cocurricular_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_id')->constrained('trainees')->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained('cocurricular_activities')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->string('role')->default('member');           // member, secretary, vice_president, president
            $table->unsignedTinyInteger('units_earned')->default(0);
            $table->unsignedTinyInteger('evaluation_score')->nullable(); // 0..100
            $table->timestamps();
            $table->unique(['trainee_id', 'activity_id', 'semester_id'], 'cocurricular_participations_natural');
            $table->index(['trainee_id', 'semester_id']);
        });

        // ----- Research projects (FYP) -----
        Schema::create('research_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_id')->constrained('trainees')->cascadeOnDelete()->unique(); // one per trainee in v1
            $table->foreignId('supervisor_pensyarah_id')->nullable()->constrained('pensyarahs')->nullOnDelete();
            $table->string('title');
            $table->string('status')->default('proposal'); // proposal, in_progress, submitted, evaluated
            $table->date('started_at')->nullable();
            $table->date('submitted_at')->nullable();
            $table->json('milestones')->nullable();          // [{label, due_date, done_at?}, ...]
            $table->unsignedTinyInteger('evaluation_score')->nullable();
            $table->timestamps();
            $table->index(['supervisor_pensyarah_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_projects');
        Schema::dropIfExists('cocurricular_participations');
        Schema::dropIfExists('cocurricular_activities');
        Schema::dropIfExists('transcript_entries');
        Schema::dropIfExists('courses');
    }
};
