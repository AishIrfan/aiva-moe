<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventLetter extends Model
{
    protected $guarded = [];
    public function event(): BelongsTo { return $this->belongsTo(ManagementEvent::class, 'management_event_id'); }
}
