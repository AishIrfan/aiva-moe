<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $guarded = [];
    protected $casts = [
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
        'payload' => 'array',
    ];

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function camera(): BelongsTo { return $this->belongsTo(Camera::class); }
    public function zone(): BelongsTo { return $this->belongsTo(Zone::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
