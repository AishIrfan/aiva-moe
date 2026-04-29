<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Practicum Window — operational anchor for a practicum cycle.
 *
 * Per IPG_WORKFLOWS.md §W3.1:
 *  - Penyelaras Praktikum opens it, links eligible cohorts, sets dates, activates
 *  - Activation unlocks W3.2 (placements) and W3.3 (supervisor assignment)
 *  - Window === phase for v1 (phase encoded in the `name` field, no enum)
 *
 * State machine: draft → active → closed (or cancelled before any confirmed placement)
 */
class PracticumWindow extends Model
{
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_CLOSED    = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $guarded = [];
    protected $casts = [
        'start_date'    => 'date',
        'end_date'      => 'date',
        'subject_scope' => 'array',
    ];

    public function campus(): BelongsTo  { return $this->belongsTo(Campus::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function updatedBy(): BelongsTo { return $this->belongsTo(User::class, 'updated_by_user_id'); }

    /** Eligible cohorts (many-to-many via practicum_window_cohorts). */
    public function cohorts(): BelongsToMany
    {
        return $this->belongsToMany(Cohort::class, 'practicum_window_cohorts');
    }

    /** Placements made within this window. */
    public function placements(): HasMany
    {
        return $this->hasMany(Placement::class);
    }

    /** Eligible trainees, derived from cohort membership. */
    public function eligibleTrainees()
    {
        return Trainee::whereIn('cohort_id', $this->cohorts()->pluck('cohorts.id'));
    }

    public function isActive(): bool { return $this->status === self::STATUS_ACTIVE; }
}
