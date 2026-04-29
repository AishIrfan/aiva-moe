<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostelAssignment extends Model
{
    protected $guarded = [];
    protected $casts = [
        'checked_in_at'  => 'date',
        'checked_out_at' => 'date',
    ];

    public function trainee(): BelongsTo  { return $this->belongsTo(Trainee::class); }
    public function room(): BelongsTo     { return $this->belongsTo(HostelRoom::class, 'room_id'); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
}
