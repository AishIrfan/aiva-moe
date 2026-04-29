<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Offline-graded gradebook entry. No submission, no deadline — just a column
 * in the gradebook for marks the lecturer enters by hand (paper exam,
 * participation, bonus). Carved out from Assessment so the assessments table
 * doesn't carry always-null lifecycle columns for these.
 */
class GradebookColumn extends Model
{
    public const KIND_MANUAL        = 'manual';
    public const KIND_PARTICIPATION = 'participation';
    public const KIND_BONUS         = 'bonus';

    public const KINDS = [self::KIND_MANUAL, self::KIND_PARTICIPATION, self::KIND_BONUS];

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED  = 'archived';

    protected $guarded = [];
    protected $casts   = [
        'total_marks' => 'decimal:2',
        'weight_pct'  => 'decimal:2',
    ];

    public function courseOffering(): BelongsTo { return $this->belongsTo(CourseOffering::class); }
    public function createdBy(): BelongsTo      { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function updatedBy(): BelongsTo      { return $this->belongsTo(User::class, 'updated_by_user_id'); }
}
