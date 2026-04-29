<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $guarded = [];
    protected $casts = ['meta' => 'array'];

    public function campus(): BelongsTo { return $this->belongsTo(Campus::class); }
    public function transcriptEntries(): HasMany { return $this->hasMany(TranscriptEntry::class); }
    public function offerings(): HasMany { return $this->hasMany(CourseOffering::class); }
}
