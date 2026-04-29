<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    protected $guarded = [];
    protected $casts = [
        'polygon' => 'array',
        'thresholds' => 'array',
    ];

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function cameras(): HasMany { return $this->hasMany(Camera::class); }
}
