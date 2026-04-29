<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Course material attached to a CourseOffering. Holds 1..N files via the
 * adjunct `course_material_files` table. Defaults to hidden_draft so lecturers
 * must explicitly choose to publish (W1.7.1).
 */
class CourseMaterial extends Model
{
    public const VISIBILITY_VISIBLE      = 'visible';
    public const VISIBILITY_HIDDEN_DRAFT = 'hidden_draft';

    protected $guarded = [];
    protected $casts   = [
        'week_number' => 'integer',
        'sort_order'  => 'integer',
    ];

    public function offering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class, 'course_offering_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseMaterialCategory::class, 'course_material_category_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(CourseMaterialFile::class);
    }

    /** Files that supersede prior versions — what trainees actually see. */
    public function activeFiles(): HasMany
    {
        return $this->hasMany(CourseMaterialFile::class)->whereNull('replaced_at');
    }

    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function updatedBy(): BelongsTo { return $this->belongsTo(User::class, 'updated_by_user_id'); }

    public function isVisibleToTrainees(): bool
    {
        return $this->visibility === self::VISIBILITY_VISIBLE;
    }
}
