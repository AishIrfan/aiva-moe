<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssistanceProgram extends Model
{
    protected $guarded = [];
    protected $casts = [
        'opens_on' => 'date',
        'closes_on' => 'date',
        'amount' => 'decimal:2',
    ];

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function applications(): HasMany { return $this->hasMany(AssistanceApplication::class); }
}
