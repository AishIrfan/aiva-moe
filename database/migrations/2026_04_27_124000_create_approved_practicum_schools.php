<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Approved Practicum Schools — IPG Admin-maintained registry.
 *
 * Per IPG_WORKFLOWS.md §W3.2: Penyelaras Praktikum picks host schools from
 * a pre-curated list, NOT the entire School-mode school registry. IPG Admin
 * curates that list per campus (a school approved at one IPG may not be
 * approved at another).
 *
 * Cross-mode: this is the cleanest read-only bridge between IPG mode (which
 * owns the registry) and School mode (which owns the canonical school records).
 * The `school_id` column is a HARD foreign key to `schools.id` so removing a
 * school nulls/cascades approvals deterministically.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approved_practicum_schools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            // Hard cross-mode FK: removing a school removes its approval rows.
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedSmallInteger('default_capacity')->default(4);
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['campus_id', 'school_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approved_practicum_schools');
    }
};
