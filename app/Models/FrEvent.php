<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FrEvent extends Model
{
    protected $guarded = [];
    protected $casts = [
        'detected_at' => 'datetime',
        'payload' => 'array',
    ];

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function camera(): BelongsTo { return $this->belongsTo(Camera::class); }
    public function attributes(): HasMany { return $this->hasMany(FrEventAttribute::class); }
    public function triggerTargets(): HasMany { return $this->hasMany(FrEventTriggerTarget::class); }
}
