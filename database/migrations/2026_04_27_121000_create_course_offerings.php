<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Course Offerings — keystone for Hat 1 (Lecturer) workflows.
 *
 * Bridges Course × Cohort × Semester × Pensyarah. This is what every Hat-1
 * workflow keys off:
 *   - W1.1 attendance (sessions hang off offerings)
 *   - W1.2 grades / gradebook
 *   - W1.3 timetable
 *   - W1.7 mini-LMS (materials, coursework, assessments)
 *
 * Uniqueness: one offering per (course, cohort, semester). Single primary
 * lecturer for v1 — co-teaching deferred (would be a `course_offering_lecturers`
 * pivot when needed).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('cohort_id')->constrained('cohorts')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('lecturer_pensyarah_id')->nullable()->constrained('pensyarahs')->nullOnDelete();
            $table->string('status')->default('active'); // active | archived
            $table->json('meta')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // One offering per (course, cohort, semester). Lecturer can change
            // mid-semester without violating this — that's just an update.
            $table->unique(['course_id', 'cohort_id', 'semester_id'], 'course_offerings_natural');
            $table->index(['lecturer_pensyarah_id', 'status']);
            $table->index(['cohort_id', 'semester_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_offerings');
    }
};
