<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FrEventAttribute extends Model
{
    protected $guarded = [];
    public function event(): BelongsTo { return $this->belongsTo(FrEvent::class, 'fr_event_id'); }
}
