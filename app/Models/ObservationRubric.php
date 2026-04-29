<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Observation Rubric — BPG-managed, versioned.
 *
 * Each Campus pins its current active rubric via `campuses.current_observation_rubric_id`.
 * Wave 3 will copy the rubric_id onto each Observation row so finalised
 * observations remain immutable when BPG publishes a new version.
 */
class ObservationRubric extends Model
{
    public const STATUS_DRAFT   = 'draft';
    public const STATUS_ACTIVE  = 'active';
    public const STATUS_RETIRED = 'retired';

    protected $guarded = [];
    protected $casts = [
        'applied_from' => 'date',
        'applied_to'   => 'date',
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(ObservationRubricCategory::class)->orderBy('display_order');
    }

    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function updatedBy(): BelongsTo { return $this->belongsTo(User::class, 'updated_by_user_id'); }

    public function isActive(): bool { return $this->status === self::STATUS_ACTIVE; }

    public function getMaxTotalScoreAttribute(): int
    {
        return (int) $this->categories->sum('max_score');
    }
}
