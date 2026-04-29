<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pensyarah extends Model
{
    protected $guarded = [];
    protected $casts = [
        'is_practicum_coordinator' => 'boolean',
        'is_ketua_jabatan'         => 'boolean',
        'meta'                     => 'array',
    ];

    public function campus(): BelongsTo { return $this->belongsTo(Campus::class); }
    public function user(): BelongsTo   { return $this->belongsTo(User::class); }

    /** Placements where this pensyarah is the assigned supervisor (Pensyarah Penyelia). */
    public function placements(): HasMany { return $this->hasMany(Placement::class, 'supervisor_pensyarah_id'); }

    /** Course offerings where this pensyarah is the primary lecturer. */
    public function courseOfferings(): HasMany { return $this->hasMany(CourseOffering::class, 'lecturer_pensyarah_id'); }

    /** Attendance sessions where this pensyarah actually ran the class (may include substitute appearances). */
    public function recordedAttendanceSessions(): HasMany
    {
        return $this->hasMany(IpgAttendanceSession::class, 'recorded_by_pensyarah_id');
    }

    /** Per-course impact responses this pensyarah has given on trainee leave requests. */
    public function leaveRequestResponses(): HasMany
    {
        return $this->hasMany(IpgLeaveRequestPensyarahResponse::class);
    }

    /** Disciplinary cases this pensyarah filed against trainees. */
    public function filedDisciplineCases(): HasMany
    {
        return $this->hasMany(IpgDisciplineCase::class, 'filed_by_pensyarah_id');
    }

    /**
     * True when this Pensyarah is the campus Penyelaras Praktikum and unlocks
     * campus-wide practicum oversight (placements, supervisor assignments,
     * coordination correspondence).
     */
    public function isPracticumCoordinator(): bool
    {
        return (bool) $this->is_practicum_coordinator;
    }

    /**
     * Maximum trainees a single Pensyarah may supervise concurrently. Soft cap
     * enforced by the Penyelaras Praktikum supervisor-assignment workflow
     * (W3.3) — overridable with a recorded reason. Sourced from config/ipg.php.
     */
    public static function maxTraineeLoad(): int
    {
        return (int) config('ipg.pensyarah.max_trainee_load', 8);
    }
}
