<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlacementLetter extends Model
{
    protected $guarded = [];
    protected $casts = [
        'sent_at'         => 'date',
        'acknowledged_at' => 'date',
    ];

    public function placement(): BelongsTo { return $this->belongsTo(Placement::class); }
}
