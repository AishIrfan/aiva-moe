<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============== CORE ACADEMIC ENTITIES ==============

        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('principal')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('level')->default(0);
            $table->timestamps();
        });

        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('ic_number')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('subject_specialization')->nullable();
            $table->timestamps();
        });

        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('homeroom_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('name');
            $table->integer('capacity')->default(40);
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('academic_year');
            $table->string('name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();
        });

        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('period_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 1=Mon … 7=Sun
            $table->string('room')->nullable();
            $table->string('kind')->default('regular'); // regular | replacement | relief
            $table->foreignId('replaces_schedule_id')->nullable()->constrained('schedules')->nullOnDelete();
            $table->date('effective_date')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'day_of_week']);
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('student_number')->nullable();
            $table->string('name');
            $table->string('ic_number')->nullable();
            $table->string('gender', 10)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('race')->nullable();
            $table->string('religion')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('status')->default('active'); // active | transferred | graduated
            $table->timestamps();
            $table->index('student_number');
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'is_active']);
        });

        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('relationship')->nullable();
            $table->string('ic_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        // ============== OPERATIONAL / SAFETY ==============

        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('indoor'); // indoor | outdoor | restricted
            $table->json('polygon')->nullable();
            $table->json('thresholds')->nullable();
            $table->timestamps();
        });

        Schema::create('cameras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('serial')->nullable()->unique();
            $table->string('stream_url')->nullable();
            $table->boolean('online')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('camera_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camera_id')->constrained()->cascadeOnDelete();
            $table->json('thresholds')->nullable();
            $table->integer('retention_days')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('camera_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // alert, incident, aiva, fr, bat, manual
            $table->string('severity')->default('info'); // info | warn | critical
            $table->string('status')->default('open'); // open | acknowledged | escalated | closed
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('assigned_to')->nullable();
            $table->timestamp('detected_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'status']);
            $table->index(['type', 'severity']);
        });

        Schema::create('broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel')->default('general'); // safety | parents | staff | general
            $table->string('title');
            $table->text('body')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // attendance | incident | analytics | custom
            $table->string('title');
            $table->string('period')->nullable(); // e.g. 2026-04, 2026-Q2
            $table->string('file_path')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['auditable_type', 'auditable_id']);
        });

        Schema::create('absent_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('label');
            $table->boolean('counts_as_present')->default(false);
            $table->boolean('is_excused')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('attendance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->string('status')->default('present'); // present | absent | late | leave | mc
            $table->foreignId('absent_reason_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->string('source')->default('auto'); // auto | manual | override | leave
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['student_id', 'date']);
            $table->index(['school_id', 'date']);
        });

        // ============== STUDENT SERVICES ==============

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('from_date');
            $table->date('to_date');
            $table->string('type')->default('personal'); // personal | medical | family
            $table->text('reason')->nullable();
            $table->string('status')->default('pending'); // pending | approved | rejected | cancelled
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->text('decision_note')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'status']);
        });

        Schema::create('leave_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category')->default('cuti'); // cuti | mc
            $table->date('from_date');
            $table->date('to_date');
            $table->integer('day_count')->default(0);
            $table->text('reason')->nullable();
            $table->string('status')->default('draft'); // draft | submitted | pending_review | approved | rejected | cancelled | returned_for_revision
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'status']);
            $table->index(['student_id', 'from_date']);
        });

        Schema::create('leave_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_submission_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();
        });

        Schema::create('discipline_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('case_number')->nullable()->unique();
            $table->string('category'); // bullying | absenteeism | misconduct | uniform | other
            $table->string('severity')->default('low');
            $table->string('status')->default('draft'); // draft | submitted | pending_review | under_investigation | action_required | resolved | closed | rejected | cancelled
            $table->date('incident_date');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->json('repeat_of')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'status']);
        });

        Schema::create('discipline_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discipline_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type'); // warning | suspension | counseling_referral | parent_call | detention | other
            $table->text('note')->nullable();
            $table->timestamp('taken_at')->nullable();
            $table->timestamps();
        });

        Schema::create('discipline_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discipline_case_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();
        });

        Schema::create('student_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category')->default('general'); // general | academic | behavioral | medical
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('assistance_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('code')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status')->default('active'); // active | paused | archived
            $table->date('opens_on')->nullable();
            $table->date('closes_on')->nullable();
            $table->timestamps();
        });

        Schema::create('assistance_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assistance_program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('submitted'); // submitted | verified | approved | rejected | disbursed
            $table->json('household_data')->nullable();
            $table->decimal('requested_amount', 12, 2)->nullable();
            $table->decimal('approved_amount', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('category')->default('general');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->string('mime_type')->nullable();
            $table->boolean('requires_ack')->default(false);
            $table->timestamps();
        });

        Schema::create('document_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->string('linkable_type'); // App\Models\SchoolClass | App\Models\Student
            $table->unsignedBigInteger('linkable_id');
            $table->timestamps();
            $table->index(['linkable_type', 'linkable_id']);
        });

        Schema::create('document_acknowledgments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guardian_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->string('method')->nullable(); // student | guardian | digital
            $table->timestamps();
            $table->unique(['document_id', 'student_id']);
        });

        // ============== COMMUNICATION ==============

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject')->nullable();
            $table->string('status')->default('open'); // open | resolved | archived
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_role')->default('teacher'); // teacher | parent | system
            $table->text('body');
            $table->boolean('flagged')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('audience'); // all_parents | class | grade | custom
            $table->unsignedBigInteger('audience_ref_id')->nullable();
            $table->string('title');
            $table->text('body');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        // ============== MANAGEMENT EVENTS (school events) ==============

        Schema::create('management_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organizer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status')->default('draft'); // draft | pending_approval | approved | ongoing | completed | cancelled | rejected | returned_for_revision
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'status']);
        });

        Schema::create('event_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('management_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->string('role')->default('participant'); // participant | lead | helper
            $table->string('consent_status')->default('pending'); // pending | given | declined
            $table->timestamps();
            $table->unique(['management_event_id', 'student_id'], 'uniq_evt_student');
        });

        Schema::create('event_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('management_event_id')->constrained()->cascadeOnDelete();
            $table->string('template_key')->default('consent');
            $table->text('rendered_body')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });

        Schema::create('event_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('management_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('present'); // present | absent | late
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
            $table->unique(['management_event_id', 'student_id'], 'uniq_evt_att');
        });

        Schema::create('event_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('management_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('level')->default('principal'); // hod | principal | district
            $table->string('decision')->default('pending'); // pending | approved | rejected | returned
            $table->text('note')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });

        // ============== INTEGRATIONS ==============

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('scope')->default('school'); // school | user | global
            $table->string('key');
            $table->json('value')->nullable();
            $table->timestamps();
            $table->unique(['scope', 'school_id', 'user_id', 'key'], 'settings_scope_unique');
        });

        Schema::create('fr_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('camera_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_event_id')->nullable()->index();
            $table->string('person_id')->nullable()->index();
            $table->string('person_name')->nullable();
            $table->string('image_url')->nullable();
            $table->float('confidence')->nullable();
            $table->timestamp('detected_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('fr_event_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fr_event_id')->constrained()->cascadeOnDelete();
            $table->string('attribute_key');
            $table->string('attribute_value');
            $table->timestamps();
            $table->index(['fr_event_id', 'attribute_key']);
        });

        Schema::create('fr_event_trigger_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fr_event_id')->constrained()->cascadeOnDelete();
            $table->string('target_type'); // notify | broadcast | webhook
            $table->string('target_ref')->nullable();
            $table->string('status')->default('pending');
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('bat_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('camera_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_event_id')->nullable()->index();
            $table->string('behavior_type')->nullable();
            $table->integer('crowd_count')->nullable();
            $table->float('score')->nullable();
            $table->timestamp('detected_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('bat_event_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bat_event_id')->constrained()->cascadeOnDelete();
            $table->string('attribute_key');
            $table->string('attribute_value');
            $table->timestamps();
            $table->index(['bat_event_id', 'attribute_key']);
        });

        Schema::create('bat_event_trigger_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bat_event_id')->constrained()->cascadeOnDelete();
            $table->string('target_type');
            $table->string('target_ref')->nullable();
            $table->string('status')->default('pending');
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'bat_event_trigger_targets','bat_event_attributes','bat_events',
            'fr_event_trigger_targets','fr_event_attributes','fr_events',
            'settings',
            'event_approvals','event_attendance','event_letters','event_participants','management_events',
            'chat_broadcasts','messages','conversations',
            'document_acknowledgments','document_links','documents',
            'assistance_applications','assistance_programs',
            'student_notes',
            'discipline_evidence','discipline_actions','discipline_cases',
            'leave_attachments','leave_submissions','leave_requests',
            'attendance_snapshots','absent_reasons',
            'audit_logs','reports','broadcasts','events',
            'camera_configs','cameras','zones',
            'guardians','enrollments','students',
            'schedules','periods','terms','subjects','school_classes','teachers','grades','schools',
        ] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
