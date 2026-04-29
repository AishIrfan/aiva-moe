<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    protected $guarded = [];
    protected $casts = [
        'effective_date' => 'date',
        'meta' => 'array',
    ];

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function term(): BelongsTo { return $this->belongsTo(Term::class); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function period(): BelongsTo { return $this->belongsTo(Period::class); }
    public function replaces(): BelongsTo { return $this->belongsTo(Schedule::class, 'replaces_schedule_id'); }
}
