<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * IPG Discipline Cases — Wave 2 Unit E.
 *
 * Backs Pensyarah workflow W1.5 (Submit a disciplinary report).
 *
 * Five tables built together:
 *
 *   discipline_categories : lookup (Akademik / Kelakuan / Kehadiran / Etika /
 *     Lain-lain). IPG Admin can extend; deactivation via `is_active=false`
 *     keeps historical references intact while removing from new-case picker.
 *     Hard restrict on delete (categories are stable data).
 *
 *   discipline_incidents : peer-to-peer link table. The W1.5 "two Pensyarah
 *     witness the same incident, each file a report" scenario is symmetric —
 *     neither report is the parent — so we model the underlying incident as
 *     a first-class entity. Cases sharing an incident reference the same
 *     incident_id; standalone cases have incident_id=NULL.
 *
 *   ipg_discipline_cases : central record. Filed by a Pensyarah; processed
 *     by IPG Admin. Status flow: submitted → under_review → action_taken |
 *     dismissed. Severity (minor | moderate | serious) describes the incident
 *     itself; priority_flag describes queue treatment (orthogonal — IPG Admin
 *     can escalate a moderate case to priority without changing severity, and
 *     not every serious case stays priority forever). Filer identity is
 *     pensyarah_id (workflow actor); user audit is created_by_user_id.
 *     Standalone — does NOT share schema with school-mode `discipline_cases`.
 *
 *   ipg_discipline_case_evidence : adjunct, multi-file. CRITICAL per the
 *     project upload security directive — disk defaults to `local` (private,
 *     storage/app/private), MIME stored as sniffed (not extension-derived).
 *     Downloads MUST be auth-gated to authorized users only. This is the
 *     most sensitive file type in the system.
 *
 *   ipg_discipline_case_witnesses : adjunct, multi-person. Supports both
 *     internal witnesses (witness_user_id FK) and external witnesses
 *     (witness_name + witness_contact text). Service-layer validation
 *     enforces "either witness_user_id OR witness_name is set" — contact
 *     alone isn't enough to identify the witness.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discipline_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            // Deactivation path: false hides the category from new-case pickers
            // while preserving historical references. Cleaner than hard delete.
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('discipline_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamp('occurred_at');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable(); // IPG Admin's consolidated notes after reviewing all linked cases
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('occurred_at', 'discipline_incidents_occurred_idx');
        });

        Schema::create('ipg_discipline_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_id')->constrained('trainees')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            // restrictOnDelete: can't delete a category in use. Use is_active instead.
            $table->foreignId('discipline_category_id')->constrained('discipline_categories')->restrictOnDelete();
            // Peer link via shared incident; null = standalone single report.
            $table->foreignId('incident_id')->nullable()->constrained('discipline_incidents')->nullOnDelete();
            // Hard enums.
            $table->string('severity'); // minor | moderate | serious
            $table->string('status')->default('submitted'); // submitted | under_review | action_taken | dismissed
            $table->timestamp('incident_at'); // when the incident occurred (date + time)
            $table->text('description'); // narrative from filer (required)
            $table->text('recommended_action')->nullable(); // advisory; IPG Admin makes final call
            // Workflow actor (per no-derived-identity rule from feedback memory).
            $table->foreignId('filed_by_pensyarah_id')->constrained('pensyarahs')->restrictOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('decided_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->text('decision')->nullable(); // IPG Admin's notes on action_taken / dismissed
            // Orthogonal to severity. Auto-set true when severity=serious (service layer);
            // IPG Admin can flip on a non-serious case manually. Stored, not computed.
            $table->boolean('priority_flag')->default(false);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['trainee_id', 'status'],            'ipg_discipline_cases_trainee_status_idx');
            $table->index(['status', 'created_at'],            'ipg_discipline_cases_queue_idx');
            $table->index(['severity', 'priority_flag'],       'ipg_discipline_cases_severity_priority_idx');
            $table->index(['semester_id', 'incident_at'],      'ipg_discipline_cases_semester_incident_idx');
        });

        Schema::create('ipg_discipline_case_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ipg_discipline_case_id')->constrained('ipg_discipline_cases')->cascadeOnDelete();
            // CRITICAL: must be 'local' (storage/app/private) per upload security directive.
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('original_filename');
            $table->string('mime_type'); // MIME-sniffed at upload, NOT extension-derived
            $table->unsignedBigInteger('size_bytes');
            $table->text('description')->nullable(); // optional caption
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('ipg_discipline_case_id', 'ipg_discipline_case_evidence_case_idx');
        });

        Schema::create('ipg_discipline_case_witnesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ipg_discipline_case_id')->constrained('ipg_discipline_cases')->cascadeOnDelete();
            // Internal witness — set when the witness has a user account in the system.
            $table->foreignId('witness_user_id')->nullable()->constrained('users')->nullOnDelete();
            // External witness — set when no user account exists.
            $table->string('witness_name')->nullable();
            $table->string('witness_contact')->nullable(); // phone / email
            $table->text('statement')->nullable();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('ipg_discipline_case_id', 'ipg_discipline_case_witnesses_case_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipg_discipline_case_witnesses');
        Schema::dropIfExists('ipg_discipline_case_evidence');
        Schema::dropIfExists('ipg_discipline_cases');
        Schema::dropIfExists('discipline_incidents');
        Schema::dropIfExists('discipline_categories');
    }
};
