<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A single class meeting for which attendance was taken (or marked cancelled).
 * Per-session granularity, distinct from school-mode's daily attendance_snapshots.
 */
class IpgAttendanceSession extends Model
{
    public const STATUS_RECORDED  = 'recorded';
    public const STATUS_CANCELLED = 'cancelled';

    protected $guarded = [];
    protected $casts   = [
        // Pinned to 'Y-m-d' (no time component) so updateOrCreate lookups by
        // a date string match the stored value exactly. The bare 'date' cast
        // causes Laravel to write '2026-03-30 00:00:00' on insert, which then
        // fails to match a '2026-03-30' WHERE bind.
        'session_date' => 'date:Y-m-d',
        'recorded_at'  => 'datetime',
        'locked_at'    => 'datetime',
    ];

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function timetableSession(): BelongsTo
    {
        return $this->belongsTo(TimetableSession::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(Pensyarah::class, 'recorded_by_pensyarah_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(IpgAttendanceRecord::class);
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    /**
     * True when within the configurable late-edit window after recording.
     * Cancelled sessions are not editable. Manually locked sessions are not editable.
     */
    public function canStillEdit(): bool
    {
        if ($this->status === self::STATUS_CANCELLED) return false;
        if ($this->isLocked()) return false;
        if ($this->recorded_at === null) return true;

        $thresholdDays = (int) config('ipg.attendance.late_edit_threshold_days', 3);
        return $this->recorded_at->diffInDays(now()) <= $thresholdDays;
    }
}
