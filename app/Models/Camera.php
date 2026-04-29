<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Camera extends Model
{
    protected $guarded = [];
    protected $casts = [
        'online' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function zone(): BelongsTo { return $this->belongsTo(Zone::class); }
    public function config(): HasOne { return $this->hasOne(CameraConfig::class); }
}
