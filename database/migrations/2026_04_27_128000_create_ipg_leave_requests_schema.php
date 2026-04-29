<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * IPG Leave / MC Requests — Wave 2 Unit D.
 *
 * Backs Pensyarah workflow W1.4 (Review and respond to a trainee's leave/MC
 * request). Trainee owns the request; IPG Admin processes the parent
 * approval; affected Pensyarah lecturers each give a course-scoped impact
 * response on a separate row.
 *
 *   ipg_leave_requests : the central record. Status flow:
 *     submitted → approved | rejected | withdrawn
 *     (No `under_review` — leave decisions don't have a meaningful
 *     investigation phase. Cf. discipline cases in Unit E, which DO.)
 *
 *   ipg_leave_request_pensyarah_responses : one row per (request, course
 *     offering). The Pensyarah who responded is stored EXPLICITLY as
 *     pensyarah_id — not derived through course_offering->lecturer — so the
 *     historical record stays accurate when a substitute responds or the
 *     offering's lecturer is reassigned later.
 *     Auto-acknowledged responses (system fallback when threshold elapses)
 *     are flagged via a dedicated bool, not inferred from null user ids.
 *
 * Service-layer behavior to wire in a later wave (NOT in this unit):
 *   - The auto-acknowledge worker that fills response rows when
 *     response_threshold_at elapses without a Pensyarah response.
 *   - The auto-excuse-from-approved-leave behavior on attendance — and that
 *     behavior MUST NOT overwrite an attendance status a lecturer has already
 *     manually marked. Lecturer intent always wins over derived state.
 *
 * Supporting documents (single per request) live on the project's `local`
 * disk per the upload security directive — NOT publicly served. Auth-gated
 * download required.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipg_leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_id')->constrained('trainees')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            // medical | personal | family | co_curricular | other
            // (v2 backlog: split `family` into `family` + `bereavement`.)
            $table->string('kind');
            // Pinned to date-only via cast 'date:Y-m-d' on the model — see
            // feedback note about Eloquent date casts and updateOrCreate.
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            // Single supporting document (e.g. MC scan). Sensitive — private
            // disk + auth-gated download.
            $table->string('supporting_document_disk')->nullable();
            $table->string('supporting_document_path')->nullable();
            // submitted | approved | rejected | withdrawn (no under_review)
            $table->string('status')->default('submitted');
            $table->foreignId('decided_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->text('decision_notes')->nullable();
            // Snapshot deadline = created_at + leave.response_threshold_days.
            // Stored (not computed) so it's queryable and overridable per-
            // request, mirroring the Unit C `locked_at` precedent.
            $table->timestamp('response_threshold_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['trainee_id', 'status'],   'ipg_leave_requests_trainee_status_idx');
            $table->index(['status', 'created_at'],   'ipg_leave_requests_queue_idx');
            $table->index(['semester_id', 'start_date'], 'ipg_leave_requests_semester_window_idx');
        });

        Schema::create('ipg_leave_request_pensyarah_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ipg_leave_request_id')
                ->constrained('ipg_leave_requests', 'id', 'ilrpr_request_fk')
                ->cascadeOnDelete();
            $table->foreignId('course_offering_id')
                ->constrained('course_offerings', 'id', 'ilrpr_offering_fk')
                ->cascadeOnDelete();
            // Stored EXPLICITLY (not derived through course_offering->lecturer)
            // — preserves who actually responded under substitute / reassignment.
            $table->foreignId('pensyarah_id')->constrained('pensyarahs')->cascadeOnDelete();
            // acknowledge | approve_impact | object
            $table->string('response');
            $table->text('conditions')->nullable();           // only when response=approve_impact
            $table->text('objection_reason')->nullable();     // only when response=object
            $table->timestamp('responded_at')->nullable();
            $table->foreignId('responded_by_user_id')
                ->nullable()
                ->constrained('users', 'id', 'ilrpr_responded_user_fk')
                ->nullOnDelete();
            // True when the system filled this in due to threshold elapse.
            // Stored explicitly (not inferred from responded_by_user_id IS NULL).
            $table->boolean('auto_acknowledged')->default(false);
            $table->timestamps();

            // One response per (request, offering). Pensyarah identity is
            // captured but not part of the natural key — substitute responding
            // for the offering still produces one row per offering.
            $table->unique(
                ['ipg_leave_request_id', 'course_offering_id'],
                'ipg_leave_responses_request_offering_unique'
            );
            $table->index(['course_offering_id', 'response'], 'ipg_leave_responses_offering_response_idx');
            $table->index(['pensyarah_id', 'response'],       'ipg_leave_responses_pensyarah_response_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipg_leave_request_pensyarah_responses');
        Schema::dropIfExists('ipg_leave_requests');
    }
};
