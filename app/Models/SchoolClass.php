<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $table = 'school_classes';
    protected $guarded = [];

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function grade(): BelongsTo { return $this->belongsTo(Grade::class); }
    public function homeroomTeacher(): BelongsTo { return $this->belongsTo(Teacher::class, 'homeroom_teacher_id'); }
    public function enrollments(): HasMany { return $this->hasMany(Enrollment::class); }
    public function activeEnrollments(): HasMany { return $this->hasMany(Enrollment::class)->where('is_active', true); }
    public function schedules(): HasMany { return $this->hasMany(Schedule::class); }
}
