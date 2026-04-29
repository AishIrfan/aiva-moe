<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cohort extends Model
{
    protected $guarded = [];
    protected $casts = [
        'intake_date' => 'date',
        'meta'        => 'array',
    ];

    public function campus(): BelongsTo   { return $this->belongsTo(Campus::class); }
    public function program(): BelongsTo  { return $this->belongsTo(Program::class); }
    public function trainees(): HasMany   { return $this->hasMany(Trainee::class); }
    public function practicumWindows(): BelongsToMany
    {
        return $this->belongsToMany(PracticumWindow::class, 'practicum_window_cohorts');
    }

    /** Human-readable label, e.g. "PISMP · Matematik · Ambilan Jun 2024". */
    public function getDisplayNameAttribute(): string
    {
        return implode(' · ', array_filter([
            $this->program?->code,
            $this->major,
            $this->intake_label,
        ]));
    }
}
