<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventApproval extends Model
{
    protected $guarded = [];
    protected $casts = ['decided_at' => 'datetime'];

    public function event(): BelongsTo { return $this->belongsTo(ManagementEvent::class, 'management_event_id'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approver_id'); }
}
