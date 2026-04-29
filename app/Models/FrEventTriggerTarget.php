<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FrEventTriggerTarget extends Model
{
    protected $guarded = [];
    protected $casts = ['payload' => 'array'];
    public function event(): BelongsTo { return $this->belongsTo(FrEvent::class, 'fr_event_id'); }
}
