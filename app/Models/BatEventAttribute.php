<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatEventAttribute extends Model
{
    protected $guarded = [];
    public function event(): BelongsTo { return $this->belongsTo(BatEvent::class, 'bat_event_id'); }
}
