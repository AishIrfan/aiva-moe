<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAttendance extends Model
{
    protected $table = 'event_attendance';
    protected $guarded = [];
    protected $casts = ['checked_in_at' => 'datetime'];

    public function event(): BelongsTo { return $this->belongsTo(ManagementEvent::class, 'management_event_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
