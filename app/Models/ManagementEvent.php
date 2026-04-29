<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManagementEvent extends Model
{
    protected $guarded = [];
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'meta' => 'array',
    ];

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function organizer(): BelongsTo { return $this->belongsTo(User::class, 'organizer_id'); }
    public function participants(): HasMany { return $this->hasMany(EventParticipant::class); }
    public function letters(): HasMany { return $this->hasMany(EventLetter::class); }
    public function attendance(): HasMany { return $this->hasMany(EventAttendance::class); }
    public function approvals(): HasMany { return $this->hasMany(EventApproval::class); }
}
