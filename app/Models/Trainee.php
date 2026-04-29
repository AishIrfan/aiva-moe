<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Trainee extends Model
{
    protected $guarded = [];
    protected $casts = [
        'date_of_birth' => 'date',
        'meta'          => 'array',
    ];

    public function campus(): BelongsTo { return $this->belongsTo(Campus::class); }
    public function cohort(): BelongsTo { return $this->belongsTo(Cohort::class); }
    public function user(): BelongsTo   { return $this->belongsTo(User::class); }

    // Phase 4 relations
    public function transcriptEntries(): HasMany       { return $this->hasMany(TranscriptEntry::class); }
    public function cocurricularParticipations(): HasMany { return $this->hasMany(CocurricularParticipation::class); }
    public function researchProject(): HasOne          { return $this->hasOne(ResearchProject::class); }

    // Phase 5 relations
    public function placements(): HasMany              { return $this->hasMany(Placement::class); }

    // Phase 7 relations
    public function hostelAssignments(): HasMany       { return $this->hasMany(HostelAssignment::class); }

    // Wave 2 Unit C
    public function attendanceRecords(): HasMany       { return $this->hasMany(IpgAttendanceRecord::class); }

    // Wave 2 Unit D
    public function leaveRequests(): HasMany           { return $this->hasMany(IpgLeaveRequest::class); }

    // Wave 2 Unit E
    public function disciplineCases(): HasMany         { return $this->hasMany(IpgDisciplineCase::class); }
}
