<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Observation Rubrics — BPG-managed, campus-pinned.
 *
 * Per IPG_WORKFLOWS.md §W2.3 / §W2.5: the practicum observation rubric is
 * BPG-configured and locked at campus level. We model this as global rubric
 * versions with each Campus pointing at the active version via FK.
 *
 * Versioning: when BPG publishes a new rubric, observations finalised under
 * the old version remain immutable. Wave 3 (Observation/Evaluation expansion)
 * will store the rubric_id ON the observation row itself so that evaluation
 * snapshots are self-contained.
 *
 * Categories carry a per-row max_score so the author defines the scoring scale
 * (no global enum — rubric author may choose 1–4, 1–5, 0–10, etc.).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observation_rubrics', function (Blueprint $table) {
            $table->id();
            $table->string('name');               // e.g. "BPG Praktikum Rubric"
            $table->string('version');            // e.g. "v2025.1"
            $table->string('status')->default('draft'); // draft | active | retired
            $table->date('applied_from')->nullable();
            $table->date('applied_to')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['name', 'version']);
            $table->index('status');
        });

        Schema::create('observation_rubric_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observation_rubric_id')->constrained('observation_rubrics')->cascadeOnDelete();
            $table->string('label');              // e.g. "Lesson Planning"
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('max_score'); // author-defined scale (e.g. 4 for 1..4)
            $table->unsignedTinyInteger('display_order')->default(0);
            $table->timestamps();

            $table->unique(['observation_rubric_id', 'label'], 'orc_rubric_label_unique');
            $table->index(['observation_rubric_id', 'display_order'], 'orc_rubric_order_idx');
        });

        // Each Campus pins its current active rubric. Nullable — campuses
        // without one fall back to draft observations only.
        Schema::table('campuses', function (Blueprint $table) {
            $table->foreignId('current_observation_rubric_id')
                ->nullable()
                ->after('meta')
                ->constrained('observation_rubrics')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('campuses', function (Blueprint $table) {
            $table->dropForeign(['current_observation_rubric_id']);
            $table->dropColumn('current_observation_rubric_id');
        });
        Schema::dropIfExists('observation_rubric_categories');
        Schema::dropIfExists('observation_rubrics');
    }
};
