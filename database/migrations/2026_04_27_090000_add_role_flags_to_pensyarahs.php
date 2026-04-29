<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds Ketua Jabatan flag + major-scope column to pensyarahs.
 *
 * Per IPG_MODE_CHECKLIST §1, Ketua Jabatan is a Pensyarah with extra
 * responsibility scoped to a single pengkhususan (major). Modeled as flags
 * on the existing Pensyarah row rather than a separate table — same identity,
 * just additional capabilities.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pensyarahs', function (Blueprint $table) {
            $table->boolean('is_ketua_jabatan')->default(false)->after('is_practicum_coordinator');
            $table->string('major_scope')->nullable()->after('is_ketua_jabatan');
            $table->index(['campus_id', 'is_ketua_jabatan']);
        });
    }

    public function down(): void
    {
        Schema::table('pensyarahs', function (Blueprint $table) {
            $table->dropIndex(['campus_id', 'is_ketua_jabatan']);
            $table->dropColumn(['is_ketua_jabatan', 'major_scope']);
        });
    }
};
