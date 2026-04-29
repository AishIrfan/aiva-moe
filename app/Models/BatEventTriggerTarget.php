<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatEventTriggerTarget extends Model
{
    protected $guarded = [];
    protected $casts = ['payload' => 'array'];
    public function event(): BelongsTo { return $this->belongsTo(BatEvent::class, 'bat_event_id'); }
}
