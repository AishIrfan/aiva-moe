<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * IPG-mode disciplinary case. Filed by a Pensyarah; processed by IPG Admin.
 *
 * Severity describes the incident itself (minor | moderate | serious).
 * priority_flag describes queue treatment (orthogonal — set automatically by
 * the service layer when severity=serious, but IPG Admin may toggle manually
 * to escalate moderate cases or de-escalate previously-serious ones).
 *
 * Standalone — does NOT share schema with school-mode `discipline_cases`.
 */
class IpgDisciplineCase extends Model
{
    public const SEVERITY_MINOR    = 'minor';
    public const SEVERITY_MODERATE = 'moderate';
    public const SEVERITY_SERIOUS  = 'serious';

    public const SEVERITIES = [self::SEVERITY_MINOR, self::SEVERITY_MODERATE, self::SEVERITY_SERIOUS];

    public const STATUS_SUBMITTED    = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_ACTION_TAKEN = 'action_taken';
    public const STATUS_DISMISSED    = 'dismissed';

    /** Terminal states — decided by IPG Admin, no further auto transitions. */
    public const STATUSES_FINAL = [self::STATUS_ACTION_TAKEN, self::STATUS_DISMISSED];

    protected $guarded = [];
    protected $casts = [
        'incident_at'   => 'datetime',
        'reviewed_at'   => 'datetime',
        'decided_at'    => 'datetime',
        'priority_flag' => 'boolean',
    ];

    public function trainee(): BelongsTo  { return $this->belongsTo(Trainee::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function category(): BelongsTo { return $this->belongsTo(DisciplineCategory::class, 'discipline_category_id'); }
    public function incident(): BelongsTo { return $this->belongsTo(DisciplineIncident::class, 'incident_id'); }
    public function filedBy(): BelongsTo  { return $this->belongsTo(Pensyarah::class, 'filed_by_pensyarah_id'); }
    public function reviewedBy(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by_user_id'); }
    public function decidedBy(): BelongsTo  { return $this->belongsTo(User::class, 'decided_by_user_id'); }
    public function createdBy(): BelongsTo  { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function updatedBy(): BelongsTo  { return $this->belongsTo(User::class, 'updated_by_user_id'); }

    public function evidence(): HasMany  { return $this->hasMany(IpgDisciplineCaseEvidence::class); }
    public function witnesses(): HasMany { return $this->hasMany(IpgDisciplineCaseWitness::class); }

    /** Sibling cases sharing the same incident (excludes self). */
    public function siblingCases()
    {
        if ($this->incident_id === null) {
            return collect();
        }
        return self::where('incident_id', $this->incident_id)
            ->where('id', '!=', $this->id)
            ->get();
    }

    public function isSerious(): bool { return $this->severity === self::SEVERITY_SERIOUS; }
    public function isFinal(): bool   { return in_array($this->status, self::STATUSES_FINAL, true); }
}
