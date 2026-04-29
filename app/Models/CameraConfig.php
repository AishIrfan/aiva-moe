<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CameraConfig extends Model
{
    protected $guarded = [];
    protected $casts = [
        'thresholds' => 'array',
        'settings' => 'array',
    ];

    public function camera(): BelongsTo { return $this->belongsTo(Camera::class); }
}
