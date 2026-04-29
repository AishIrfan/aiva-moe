<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One attendance record per trainee per session. Status is a hard enum;
 * `excused_mc` and `excused_leave` are recorded labels — the auto-fill from
 * approved Surat Cuti / MC requests gets wired in Unit D and must NOT
 * overwrite a manually-set status.
 */
class IpgAttendanceRecord extends Model
{
    public const STATUS_PRESENT        = 'present';
    public const STATUS_ABSENT         = 'absent';
    public const STATUS_LATE           = 'late';
    public const STATUS_EXCUSED_MC     = 'excused_mc';
    public const STATUS_EXCUSED_LEAVE  = 'excused_leave';

    public const STATUSES = [
        self::STATUS_PRESENT,
        self::STATUS_ABSENT,
        self::STATUS_LATE,
        self::STATUS_EXCUSED_MC,
        self::STATUS_EXCUSED_LEAVE,
    ];

    protected $guarded = [];
    protected $casts   = ['minutes_late' => 'integer'];

    public function session(): BelongsTo
    {
        return $this->belongsTo(IpgAttendanceSession::class, 'ipg_attendance_session_id');
    }

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class);
    }

    public function isExcused(): bool
    {
        return in_array($this->status, [self::STATUS_EXCUSED_MC, self::STATUS_EXCUSED_LEAVE], true);
    }
}
