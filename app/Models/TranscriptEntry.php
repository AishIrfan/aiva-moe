<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TranscriptEntry extends Model
{
    protected $guarded = [];
    protected $casts = ['grade_point' => 'decimal:2'];

    public function trainee(): BelongsTo  { return $this->belongsTo(Trainee::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function course(): BelongsTo   { return $this->belongsTo(Course::class); }
}
