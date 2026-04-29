<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Timetable Session — a recurring weekly slot for a Course Offering.
 *
 * day_of_week: 1=Mon ... 7=Sun (ISO 8601).
 *
 * Distinct from `IpgAttendanceSession`, which records an actual held instance
 * of a class on a specific date (and may exist without a TimetableSession when
 * ad-hoc — e.g. a substitute or makeup class).
 */
class TimetableSession extends Model
{
    public const DAY_MONDAY    = 1;
    public const DAY_TUESDAY   = 2;
    public const DAY_WEDNESDAY = 3;
    public const DAY_THURSDAY  = 4;
    public const DAY_FRIDAY    = 5;
    public const DAY_SATURDAY  = 6;
    public const DAY_SUNDAY    = 7;

    protected $guarded = [];
    protected $casts   = ['meta' => 'array'];

    public function courseOffering(): BelongsTo { return $this->belongsTo(CourseOffering::class); }
    public function createdBy(): BelongsTo      { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function updatedBy(): BelongsTo      { return $this->belongsTo(User::class, 'updated_by_user_id'); }

    public function attendanceSessions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(IpgAttendanceSession::class);
    }
}
