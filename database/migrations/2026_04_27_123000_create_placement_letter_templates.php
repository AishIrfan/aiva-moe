<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Placement Letter Templates — BPG-managed, campus-pinned.
 *
 * Per IPG_WORKFLOWS.md §W3.4: placement letters are generated from a BPG-locked
 * template at campus level. Same versioning pattern as observation rubrics.
 *
 * `body` is plain text with named placeholders like `{trainee_name}`,
 * `{host_school_name}`, `{principal_name}`, `{start_date}`, `{end_date}`,
 * `{trainee_list}`, `{supervisor_list}`, `{ipg_contact}`. Rendering is simple
 * str_replace at dispatch time — we deliberately don't store executable Blade
 * in the database.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('placement_letter_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');                // e.g. "BPG Standard Placement Letter"
            $table->string('version');             // e.g. "v2025.1"
            $table->string('status')->default('draft'); // draft | active | retired
            $table->date('applied_from')->nullable();
            $table->date('applied_to')->nullable();
            $table->string('subject_line')->nullable();
            $table->longText('body');              // text with {placeholder} tokens
            $table->json('available_placeholders')->nullable(); // list of placeholders this template uses
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['name', 'version']);
            $table->index('status');
        });

        Schema::table('campuses', function (Blueprint $table) {
            $table->foreignId('current_placement_letter_template_id')
                ->nullable()
                ->after('current_observation_rubric_id')
                ->constrained('placement_letter_templates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('campuses', function (Blueprint $table) {
            $table->dropForeign(['current_placement_letter_template_id']);
            $table->dropColumn('current_placement_letter_template_id');
        });
        Schema::dropIfExists('placement_letter_templates');
    }
};
