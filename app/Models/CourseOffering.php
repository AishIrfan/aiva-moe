<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Course Offering — Course × Cohort × Semester × Lecturer.
 *
 * Keystone for all Hat 1 (Lecturer) workflows. Every assessment, material,
 * timetable session, and attendance session in IPG mode hangs off here.
 */
class CourseOffering extends Model
{
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_ARCHIVED = 'archived';

    protected $guarded = [];
    protected $casts   = ['meta' => 'array'];

    public function course(): BelongsTo   { return $this->belongsTo(Course::class); }
    public function cohort(): BelongsTo   { return $this->belongsTo(Cohort::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function lecturer(): BelongsTo { return $this->belongsTo(Pensyarah::class, 'lecturer_pensyarah_id'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function updatedBy(): BelongsTo { return $this->belongsTo(User::class, 'updated_by_user_id'); }

    public function timetableSessions(): HasMany
    {
        return $this->hasMany(TimetableSession::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(CourseMaterial::class);
    }

    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(IpgAttendanceSession::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function gradebookColumns(): HasMany
    {
        return $this->hasMany(GradebookColumn::class);
    }

    public function leaveRequestResponses(): HasMany
    {
        return $this->hasMany(IpgLeaveRequestPensyarahResponse::class);
    }

    /** Convenience accessor for sidebar/UI display ("MTE-3023 · Matematik · Sem 2"). */
    public function getDisplayLabelAttribute(): string
    {
        return implode(' · ', array_filter([
            $this->course?->code,
            $this->cohort?->major,
            $this->semester?->code,
        ]));
    }
}
