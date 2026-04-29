<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    protected $guarded = [];
    protected $casts = ['date_of_birth' => 'date'];

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function enrollments(): HasMany { return $this->hasMany(Enrollment::class); }
    public function activeEnrollment(): HasOne { return $this->hasOne(Enrollment::class)->where('is_active', true); }
    public function guardians(): HasMany { return $this->hasMany(Guardian::class); }
    public function primaryGuardian(): HasOne { return $this->hasOne(Guardian::class)->where('is_primary', true); }
    public function events(): HasMany { return $this->hasMany(Event::class); }
    public function notes(): HasMany { return $this->hasMany(StudentNote::class); }
    public function attendance(): HasMany { return $this->hasMany(AttendanceSnapshot::class); }
    public function leaveRequests(): HasMany { return $this->hasMany(LeaveRequest::class); }
    public function leaveSubmissions(): HasMany { return $this->hasMany(LeaveSubmission::class); }
    public function disciplineCases(): HasMany { return $this->hasMany(DisciplineCase::class); }
    public function assistanceApplications(): HasMany { return $this->hasMany(AssistanceApplication::class); }

    public function currentClass()
    {
        return $this->activeEnrollment?->schoolClass;
    }
}
