<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * IPG Attendance — Wave 2 Unit C.
 *
 * Per-session attendance distinct from school-mode's daily `attendance_snapshots`.
 * Backs Pensyarah workflow W1.1 (Take attendance for a class session).
 *
 * Two tables:
 *   ipg_attendance_sessions : one row per actual class meeting that attendance
 *     was taken for. Optionally linked to a recurring `timetable_sessions` row;
 *     null link = ad-hoc (substitute, makeup). Status `recorded` or `cancelled`;
 *     cancelled sessions own no records and carry a `cancellation_reason`.
 *     `recorded_by_pensyarah_id` captures who actually ran the class (may
 *     differ from the offering's primary lecturer — substitute case).
 *     `locked_at` is set explicitly when the late-edit threshold elapses
 *     (configurable via `config/ipg.php` → attendance.late_edit_threshold_days).
 *     IPG Admin can override by clearing it with an audit entry.
 *
 *   ipg_attendance_records : one row per trainee per session. Hard enum
 *     status: present | absent | late | excused_mc | excused_leave.
 *     `excused_*` is just a label — the auto-fill from approved Surat Cuti / MC
 *     gets wired in Unit D and must NOT overwrite a manually-set status.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipg_attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained('course_offerings')->cascadeOnDelete();
            // Nullable: ad-hoc / makeup sessions are not tied to a recurring slot.
            $table->foreignId('timetable_session_id')->nullable()->constrained('timetable_sessions')->nullOnDelete();
            $table->date('session_date');
            $table->time('start_time');
            $table->time('end_time');
            // 'recorded' | 'cancelled'
            $table->string('status')->default('recorded');
            $table->text('cancellation_reason')->nullable();
            // Who actually took attendance — may differ from offering->lecturer (substitute case).
            $table->foreignId('recorded_by_pensyarah_id')->nullable()->constrained('pensyarahs')->nullOnDelete();
            $table->timestamp('recorded_at')->nullable();
            // Set explicitly when late-edit threshold elapses; nullable = still editable.
            $table->timestamp('locked_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // One session per (offering, date, start_time) — accommodates rare
            // multi-session days without forcing a timetable link.
            $table->unique(
                ['course_offering_id', 'session_date', 'start_time'],
                'ipg_attendance_sessions_natural'
            );
            $table->index(['course_offering_id', 'session_date'], 'ipg_attendance_sessions_offering_date_idx');
        });

        Schema::create('ipg_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ipg_attendance_session_id')->constrained('ipg_attendance_sessions')->cascadeOnDelete();
            $table->foreignId('trainee_id')->constrained('trainees')->cascadeOnDelete();
            // Hard enum: present | absent | late | excused_mc | excused_leave
            $table->string('status');
            // Only meaningful when status='late'.
            $table->unsignedSmallInteger('minutes_late')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // One record per trainee per session — hard.
            $table->unique(
                ['ipg_attendance_session_id', 'trainee_id'],
                'ipg_attendance_records_session_trainee'
            );
            // Trainee-level absenteeism queries (chronic flags, transcripts).
            $table->index(['trainee_id', 'status'], 'ipg_attendance_records_trainee_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipg_attendance_records');
        Schema::dropIfExists('ipg_attendance_sessions');
    }
};
