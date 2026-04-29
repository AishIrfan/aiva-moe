<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Course Materials — Wave 2 Unit A.
 *
 * Backs Pensyarah workflow W1.7.1 (Upload and organize course materials).
 * Three related tables built together:
 *
 *  - course_material_categories : lookup (Course Notes / Slides / Past Year
 *    Exam Papers / References / Worksheets / Other). Global for v1; campus-
 *    scoped variants can be added later via a nullable `campus_id`.
 *
 *  - course_materials : one row per logical material item attached to a
 *    course offering. May hold multiple files. Defaults to `hidden_draft`
 *    visibility (asymmetric risk: accidentally publishing unfinished work is
 *    worse than accidentally hiding finished work).
 *
 *  - course_material_files : adjunct, supports multi-file upload per material.
 *    Stores disk + path + sniffed MIME + size. Replacement is tracked via
 *    `replaced_at`; trainee-facing queries filter on `replaced_at IS NULL`.
 *    Per the upload security directive, files default to the `local` disk
 *    (storage/app/private — outside the public web root).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_material_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('course_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained('course_offerings')->cascadeOnDelete();
            $table->foreignId('course_material_category_id')->constrained('course_material_categories')->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('week_number')->nullable();
            // visibility: 'visible' | 'hidden_draft' (default hidden_draft per Unit A decision)
            $table->string('visibility')->default('hidden_draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Hot path: trainee opens an offering and sees visible materials in order.
            $table->index(['course_offering_id', 'visibility', 'sort_order'], 'course_materials_offering_visible_idx');
        });

        Schema::create('course_material_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_material_id')->constrained('course_materials')->cascadeOnDelete();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('sort_order')->default(0);
            // Set when a newer file supersedes this one. Trainee-facing queries
            // filter on `replaced_at IS NULL`. No lineage chain in v1.
            $table->timestamp('replaced_at')->nullable();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['course_material_id', 'replaced_at'], 'course_material_files_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_material_files');
        Schema::dropIfExists('course_materials');
        Schema::dropIfExists('course_material_categories');
    }
};
