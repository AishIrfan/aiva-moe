<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-(request, course offering) response from a Pensyarah. Pensyarah identity
 * is stored EXPLICITLY (`pensyarah_id`) at write time — not derived through
 * `course_offering->lecturer` — so the historical record stays accurate when
 * a substitute responds or the lecturer is later reassigned.
 *
 * `auto_acknowledged=true` indicates the system filled this in when the
 * `response_threshold_at` on the parent request elapsed without an explicit
 * Pensyarah response (`response='acknowledge'`, `responded_by_user_id=NULL`).
 */
class IpgLeaveRequestPensyarahResponse extends Model
{
    public const RESPONSE_ACKNOWLEDGE    = 'acknowledge';
    public const RESPONSE_APPROVE_IMPACT = 'approve_impact';
    public const RESPONSE_OBJECT         = 'object';

    public const RESPONSES = [
        self::RESPONSE_ACKNOWLEDGE,
        self::RESPONSE_APPROVE_IMPACT,
        self::RESPONSE_OBJECT,
    ];

    protected $guarded = [];
    protected $casts   = [
        'responded_at'      => 'datetime',
        'auto_acknowledged' => 'boolean',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(IpgLeaveRequest::class, 'ipg_leave_request_id');
    }

    public function courseOffering(): BelongsTo { return $this->belongsTo(CourseOffering::class); }
    public function pensyarah(): BelongsTo      { return $this->belongsTo(Pensyarah::class); }
    public function respondedBy(): BelongsTo    { return $this->belongsTo(User::class, 'responded_by_user_id'); }
}
