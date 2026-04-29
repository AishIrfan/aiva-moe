<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Broadcast extends Model
{
    protected $guarded = [];
    protected $casts = ['sent_at' => 'datetime'];

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
