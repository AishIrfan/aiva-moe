<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class Recordings — School-mode video recordings of classroom sessions.
 *
 * Per CLASS_RECORDING_CHECKLIST.md §2. School-mode only — IPG mode hides
 * this feature entirely (no sidebar entry, no routes accessible).
 *
 * Default retention: 30 days from recording date, then auto-delete via
 * scheduled job (file removed, status → archived, metadata kept for audit).
 * Preserved recordings are exempt from auto-delete (sticky state until
 * manually reverted).
 *
 * Per-school configuration lives on the existing `settings` key-value
 * table (see `App\Models\Setting`) — NOT a new top-level settings
 * infrastructure. Setting keys live on the model as constants:
 *   class_recording_enabled (default false)
 *   class_recording_audio_enabled (default false)
 *   class_recording_retention_days (default 30)
 *   class_recording_max_file_size_mb (default 2048)
 *   class_recording_allowed_mime_types (default ['video/mp4'])
 *
 * Storage: `local` disk (storage/app/private — outside the web root) per
 * the project upload security directive. Path scheme uses file_uuid (NOT
 * the integer id) so URLs aren't enumerable:
 *   class_recordings/{school_id}/{Y}/{m}/{file_uuid}.{ext}
 *
 * The polymorphic `referenced_by_*` columns are forward scaffolding for
 * linking recordings to disciplinary cases / safety alerts. v1 does NOT
 * build the linking UI — but having the columns now means the linking
 * can be added later without a migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_recordings', function (Blueprint $table) {
            $table->id();
            // UUID drives the on-disk filename so paths can't be enumerated
            // by guessing sequential ids.
            $table->uuid('file_uuid')->unique();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            // Nullable: free-form classroom labelling is acceptable when the
            // school's class structure doesn't map cleanly.
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->string('subject')->nullable();
            // restrictOnDelete: don't orphan recordings when a teacher account
            // is deleted — admin must reassign first.
            $table->foreignId('teacher_user_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();

            // Stored on the project's `local` disk (storage/app/private). For
            // S3/GCS production, swap via env IPG-style — this column captures
            // which disk a given row lives on, so cross-disk migrations work.
            $table->string('file_disk')->default('local');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->string('mime_type'); // sniffed at upload, NOT extension-derived

            // Hard enum: recording | uploading | processing | ready | failed | archived
            // recording: in-progress streaming upload (smartboard path; v1 typically skips)
            // uploading: client → server transfer in progress
            // processing: post-upload processing (no-op in v1; included so adding
            //             transcoding later doesn't require an enum migration)
            // ready:     viewable
            // failed:    upload/processing error (file may or may not exist)
            // archived:  retention-deleted (file gone, metadata kept for audit)
            $table->string('status')->default('uploading');

            // Computed at upload time = started_at + retention_days (per-school).
            // Stored (not computed) so the retention job can index on it.
            $table->timestamp('retention_expires_at');

            $table->boolean('preserved')->default(false);
            $table->timestamp('preserved_at')->nullable();
            $table->foreignId('preserved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('preserved_reason')->nullable();

            // manual_upload | smartboard_upload (v2)
            $table->string('created_via')->default('manual_upload');
            // Audit trail: who initiated the row creation. Usually equals
            // teacher_user_id for manual_upload but not always (school admin
            // uploading on behalf of a teacher).
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();

            // Forward scaffolding for linking to disciplinary cases / safety alerts.
            // No UI in v1; columns reserved so future linking is a no-migration change.
            $table->string('referenced_by_type')->nullable();
            $table->unsignedBigInteger('referenced_by_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Hot path: list view filtered by school + status, ordered by started_at desc.
            $table->index(['school_id', 'status', 'started_at'], 'class_recordings_school_status_idx');
            // Teacher's own recordings view (per access tier §7.1).
            $table->index(['teacher_user_id', 'started_at'], 'class_recordings_teacher_idx');
            // Retention job: WHERE retention_expires_at < now() AND preserved = false.
            $table->index(['retention_expires_at', 'preserved'], 'class_recordings_retention_idx');
            // Polymorphic morph index for future disciplinary-case linking.
            $table->index(['referenced_by_type', 'referenced_by_id'], 'class_recordings_morph_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_recordings');
    }
};
