<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campus extends Model
{
    protected $table = 'campuses';
    protected $guarded = [];
    protected $casts = ['meta' => 'array'];

    public function semesters(): HasMany    { return $this->hasMany(Semester::class); }
    public function cohorts(): HasMany      { return $this->hasMany(Cohort::class); }
    public function trainees(): HasMany     { return $this->hasMany(Trainee::class); }
    public function pensyarahs(): HasMany   { return $this->hasMany(Pensyarah::class); }
    public function hostelBlocks(): HasMany { return $this->hasMany(HostelBlock::class); }
    public function practicumWindows(): HasMany { return $this->hasMany(PracticumWindow::class); }
    public function approvedPracticumSchools(): HasMany { return $this->hasMany(ApprovedPracticumSchool::class); }

    /** Currently pinned BPG observation rubric (Wave 1). */
    public function currentObservationRubric(): BelongsTo
    {
        return $this->belongsTo(ObservationRubric::class, 'current_observation_rubric_id');
    }

    /** Currently pinned BPG placement letter template (Wave 1). */
    public function currentPlacementLetterTemplate(): BelongsTo
    {
        return $this->belongsTo(PlacementLetterTemplate::class, 'current_placement_letter_template_id');
    }

    public function currentSemester()
    {
        return $this->semesters()->where('is_current', true)->latest('start_date')->first();
    }
}
