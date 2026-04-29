<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trainee-owned leave / MC request. IPG Admin processes the parent approval;
 * affected Pensyarah lecturers each give a course-scoped impact response on
 * the adjunct table.
 *
 * Status flow: submitted → approved | rejected | withdrawn (no under_review).
 *
 * NOTE for whoever wires the service layer in a later wave: the
 * auto-excuse-from-approved-leave behavior on attendance MUST only act on
 * unset/null attendance statuses. It MUST NOT overwrite a status a lecturer
 * has already manually marked. Lecturer intent always wins over derived state.
 */
class IpgLeaveRequest extends Model
{
    public const KIND_MEDICAL       = 'medical';
    public const KIND_PERSONAL      = 'personal';
    public const KIND_FAMILY        = 'family';
    public const KIND_CO_CURRICULAR = 'co_curricular';
    public const KIND_OTHER         = 'other';

    public const KINDS = [
        self::KIND_MEDICAL,
        self::KIND_PERSONAL,
        self::KIND_FAMILY,
        self::KIND_CO_CURRICULAR,
        self::KIND_OTHER,
    ];

    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_WITHDRAWN = 'withdrawn';

    /** Terminal states — no further transitions in normal flow. */
    public const STATUSES_FINAL = [
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_WITHDRAWN,
    ];

    protected $guarded = [];
    protected $casts   = [
        // Pin date format (Unit C lesson) so updateOrCreate lookups by date string match storage.
        'start_date'            => 'date:Y-m-d',
        'end_date'              => 'date:Y-m-d',
        'decided_at'            => 'datetime',
        'response_threshold_at' => 'datetime',
    ];

    public function trainee(): BelongsTo  { return $this->belongsTo(Trainee::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function decidedBy(): BelongsTo { return $this->belongsTo(User::class, 'decided_by_user_id'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function updatedBy(): BelongsTo { return $this->belongsTo(User::class, 'updated_by_user_id'); }

    public function pensyarahResponses(): HasMany
    {
        return $this->hasMany(IpgLeaveRequestPensyarahResponse::class);
    }

    public function isApproved(): bool { return $this->status === self::STATUS_APPROVED; }
    public function isFinal(): bool    { return in_array($this->status, self::STATUSES_FINAL, true); }
}
