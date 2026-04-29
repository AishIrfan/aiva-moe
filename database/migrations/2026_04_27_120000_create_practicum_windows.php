<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Practicum Windows — first-class entity per IPG_WORKFLOWS.md §W3.1.
 *
 * A Window is the operational anchor for a practicum cycle: Penyelaras opens
 * one, links eligible cohorts, sets a date range, and trainees are then placed
 * within it. For v1 we treat Window === phase (Fasa 1, 2, 3, Internship) — the
 * phase is encoded in the Window's name, no schema enum.
 *
 * Window vs Semester: a Placement points at BOTH — the Window for operational
 * lifecycle (open/close, capacity, supervisor assignment) and the Semester for
 * transcript posting.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practicum_windows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->string('name'); // e.g. "Praktikum PISMP Matematik Ambilan Jun 2023 — Fasa 2"
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('draft'); // draft | active | closed | cancelled
            $table->unsignedSmallInteger('default_capacity_per_school')->default(4);
            $table->json('subject_scope')->nullable();  // optional override of cohort majors
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['campus_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });

        // Many-to-many: a Window can target multiple cohorts; a Cohort can be in
        // multiple Windows over its lifecycle (Fasa 1, Fasa 2, ...).
        Schema::create('practicum_window_cohorts', function (Blueprint $table) {
            $table->foreignId('practicum_window_id')->constrained('practicum_windows')->cascadeOnDelete();
            $table->foreignId('cohort_id')->constrained('cohorts')->cascadeOnDelete();
            $table->primary(['practicum_window_id', 'cohort_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practicum_window_cohorts');
        Schema::dropIfExists('practicum_windows');
    }
};
