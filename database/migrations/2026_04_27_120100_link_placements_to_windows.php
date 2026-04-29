<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Wires existing Placements to the new Practicum Window concept.
 *
 * Adds `practicum_window_id` (nullable for backfill compatibility — IpgDemoSeeder
 * populates it) and documents the new status state machine. SQLite doesn't
 * enforce string column enums; the model-layer constants are the source of truth:
 *
 *   placed → pending_acknowledgement → confirmed → active → completed
 *   (cancelled / withdrawn from any pre-completion state)
 *
 * Placements whose status was previously 'scheduled' map to 'placed'; existing
 * 'active' / 'completed' / 'cancelled' map cleanly.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('placements', function (Blueprint $table) {
            $table->foreignId('practicum_window_id')
                ->nullable()
                ->after('semester_id')
                ->constrained('practicum_windows')
                ->nullOnDelete();
            $table->index('practicum_window_id');
        });
    }

    public function down(): void
    {
        Schema::table('placements', function (Blueprint $table) {
            $table->dropForeign(['practicum_window_id']);
            $table->dropIndex(['practicum_window_id']);
            $table->dropColumn('practicum_window_id');
        });
    }
};
