<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Coursework definition with a submission/delivery lifecycle. The `kind`
 * discriminator selects which extra columns / settings shape is meaningful.
 * Offline-graded gradebook entries (manual / participation / bonus) are NOT
 * here — see the separate GradebookColumn model.
 */
class Assessment extends Model
{
    public const KIND_ASSIGNMENT  = 'assignment';
    public const KIND_TUTORIAL    = 'tutorial';
    public const KIND_F2F_TEST    = 'f2f_test';
    public const KIND_ONLINE_TEST = 'online_test';

    public const KINDS = [
        self::KIND_ASSIGNMENT,
        self::KIND_TUTORIAL,
        self::KIND_F2F_TEST,
        self::KIND_ONLINE_TEST,
    ];

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED  = 'archived';

    public const LATE_POLICY_NOT_ALLOWED          = 'not_allowed';
    public const LATE_POLICY_ALLOWED_WITH_PENALTY = 'allowed_with_penalty';
    public const LATE_POLICY_ALLOWED_NO_PENALTY   = 'allowed_no_penalty';

    public const RESULT_RELEASE_IMMEDIATE          = 'immediate';
    public const RESULT_RELEASE_AFTER_WINDOW_CLOSE = 'after_window_close';
    public const RESULT_RELEASE_MANUAL             = 'manual';

    protected $guarded = [];
    protected $casts   = [
        'open_at'             => 'datetime',
        'due_at'              => 'datetime',
        'total_marks'         => 'decimal:2',
        'weight_pct'          => 'decimal:2',
        'late_penalty_rules'  => 'array',
        'settings'            => 'array',
    ];

    public function courseOffering(): BelongsTo { return $this->belongsTo(CourseOffering::class); }
    public function createdBy(): BelongsTo      { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function updatedBy(): BelongsTo      { return $this->belongsTo(User::class, 'updated_by_user_id'); }

    public function questions(): HasMany
    {
        return $this->hasMany(OnlineTestQuestion::class);
    }

    public function isPublished(): bool { return $this->status === self::STATUS_PUBLISHED; }
    public function isOnlineTest(): bool { return $this->kind === self::KIND_ONLINE_TEST; }
    public function isF2FTest(): bool    { return $this->kind === self::KIND_F2F_TEST; }
}
