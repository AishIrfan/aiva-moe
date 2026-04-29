<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ----- Campuses (top-level entity for IPG mode) -----
        Schema::create('campuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['state', 'district']);
        });

        // ----- Programs (PISMP, PPISMP, KPLI, PDPLI...) — extensible lookup -----
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ----- Semesters (campus-scoped academic periods) -----
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->timestamps();
            $table->unique(['campus_id', 'code']);
            $table->index(['campus_id', 'is_current']);
        });

        // ----- Cohorts (Program × Major × Intake, scoped to campus) -----
        Schema::create('cohorts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->restrictOnDelete();
            $table->string('major');                 // Pengkhususan (e.g. Matematik)
            $table->string('intake_label');          // Ambilan label (e.g. "Ambilan Jun 2024")
            $table->date('intake_date');             // Calendar anchor for the intake
            $table->string('status')->default('active'); // active, completed, dissolved
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->unique(['campus_id', 'program_id', 'major', 'intake_label'], 'cohorts_natural_key');
            $table->index(['campus_id', 'status']);
        });

        // ----- Trainees (Guru Pelatih) — canonical identity in IPG mode -----
        Schema::create('trainees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->foreignId('cohort_id')->nullable()->constrained('cohorts')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('trainee_number')->unique(); // matric / IPG ID
            $table->string('ic_number')->nullable();
            $table->string('name');
            $table->string('gender', 1)->nullable();    // 'M' | 'F'
            $table->date('date_of_birth')->nullable();
            $table->string('status')->default('active'); // active, suspended, graduated, withdrawn
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['campus_id', 'status']);
            $table->index(['cohort_id', 'status']);
        });

        // ----- Pensyarah (IPG lecturers) -----
        Schema::create('pensyarahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('staff_number')->nullable()->unique();
            $table->string('specialization')->nullable();
            $table->boolean('is_practicum_coordinator')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['campus_id', 'is_practicum_coordinator']);
        });

        // ----- Users: campus_id (nullable, peer of school_id) -----
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('campus_id')->nullable()->after('school_id');
            $table->index('campus_id');
            // Note: not adding FK to keep migrations idempotent if users predates campuses.
            // Application-level integrity is sufficient for v1.
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['campus_id']);
            $table->dropColumn('campus_id');
        });

        Schema::dropIfExists('pensyarahs');
        Schema::dropIfExists('trainees');
        Schema::dropIfExists('cohorts');
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('programs');
        Schema::dropIfExists('campuses');
    }
};
