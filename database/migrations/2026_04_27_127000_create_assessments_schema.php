<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Assessments + Gradebook Columns + Online Test Question Bank — Wave 2 Unit B.
 *
 * Backs Pensyarah workflows W1.2 (manual grading), W1.7.2 (assignment),
 * W1.7.3 (tutorial), W1.7.4 (F2F test), W1.7.5 (online test).
 *
 * Per locked decision #6 + Wave 2 ambiguity (b) + Unit B carve-out:
 *
 *   `assessments` is a unified table for true coursework with a submission /
 *   delivery lifecycle (assignment, tutorial, f2f_test, online_test). Shared
 *   columns are first-class. F2F-test-specific fields (venue, allowed_materials)
 *   and online-test-specific fields (attempts_allowed, result_release) are
 *   nullable columns directly on the table — no kind-specific adjunct tables
 *   for those. Assignment/tutorial-specific config lives in a JSON `settings`
 *   column (sparse, varied shape).
 *
 *   `gradebook_columns` is a separate table for offline-graded gradebook
 *   entries (manual / participation / bonus). These have no submission, no
 *   deadline, no submission types — putting them in `assessments` overloaded
 *   the table with always-null columns. Both feed the gradebook UI but they're
 *   distinct entities.
 *
 *   `online_test_questions` and `online_test_question_options` are proper
 *   relational adjuncts — the question bank is inherently relational and JSON
 *   would defeat any future querying / analytics.
 *
 * Trainee submissions, attempts, responses, and graded results are EXPLICITLY
 * deferred to a later wave (locked decision #13) and not modelled here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained('course_offerings')->cascadeOnDelete();
            // Hard enum. manual_grade is NOT here — it lives in gradebook_columns.
            $table->string('kind'); // assignment | tutorial | f2f_test | online_test
            $table->string('title');
            $table->text('description')->nullable();    // lecturer-facing internal note
            $table->text('instructions')->nullable();   // trainee-facing brief
            $table->decimal('total_marks', 6, 2);
            $table->decimal('weight_pct',  5, 2);
            // draft → published → archived. Archived is terminal; unarchive is
            // an IPG Admin override action (audit elsewhere).
            $table->string('status')->default('draft');
            $table->timestamp('open_at')->nullable();   // when trainees can start; null for kinds w/o open window
            $table->timestamp('due_at')->nullable();    // deadline; for f2f_test = scheduled date+time
            // not_allowed | allowed_with_penalty | allowed_no_penalty
            $table->string('late_policy')->default('not_allowed');
            // Only meaningful when late_policy=allowed_with_penalty.
            // Shape: {"grace_hours": int, "per_day_pct": int, "max_pct": int, "after_max_action": "zero"|"reject"}
            $table->json('late_penalty_rules')->nullable();

            // F2F-test-specific (nullable for other kinds)
            $table->string('venue')->nullable();
            $table->text('allowed_materials')->nullable();

            // Shared by F2F + online tests (nullable for other kinds)
            $table->unsignedSmallInteger('duration_minutes')->nullable();

            // Online-test-specific (nullable for other kinds)
            $table->unsignedSmallInteger('attempts_allowed')->nullable();
            // immediate | after_window_close | manual
            $table->string('result_release')->nullable();

            // Assignment/tutorial-specific sparse config bag.
            // Shape (assignment): {"submission_types": [...], "file_constraints": {...}, "group": {...}}
            // Shape (tutorial):   {"submission_types": [...]} or null
            $table->json('settings')->nullable();

            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['course_offering_id', 'kind', 'status'], 'assessments_offering_kind_status_idx');
            $table->index(['course_offering_id', 'due_at'],         'assessments_offering_due_idx');
        });

        Schema::create('gradebook_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained('course_offerings')->cascadeOnDelete();
            // manual | participation | bonus
            $table->string('kind');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('total_marks', 6, 2);
            $table->decimal('weight_pct',  5, 2);
            // Same lifecycle vocabulary as assessments for UX consistency.
            $table->string('status')->default('draft'); // draft | published | archived
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['course_offering_id', 'kind', 'status'], 'gradebook_columns_offering_kind_status_idx');
        });

        Schema::create('online_test_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            // mcq | short_answer
            $table->string('kind');
            $table->text('question_text');
            // Optional question image. Stored on the project's `local` disk
            // (storage/app/private — outside the public web root) per the
            // upload security directive. Auth-gated download.
            $table->string('image_disk')->nullable();
            $table->string('image_path')->nullable();
            $table->decimal('marks', 5, 2);
            $table->text('explanation')->nullable();        // shown to trainee after grading
            $table->text('suggested_answer')->nullable();   // pensyarah-only, used during manual SA grading
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['assessment_id', 'sort_order'], 'online_test_questions_assessment_order_idx');
        });

        Schema::create('online_test_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_test_question_id')->constrained('online_test_questions')->cascadeOnDelete();
            $table->text('option_text');
            // Image-bearing answer choices (common in science/math MCQs). Same
            // private-disk pattern as the parent question's image.
            $table->string('image_disk')->nullable();
            $table->string('image_path')->nullable();
            // Multi-correct supported natively — multiple options can be true.
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['online_test_question_id', 'sort_order'], 'online_test_question_options_question_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_test_question_options');
        Schema::dropIfExists('online_test_questions');
        Schema::dropIfExists('gradebook_columns');
        Schema::dropIfExists('assessments');
    }
};
