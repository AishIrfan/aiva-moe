<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplineAction extends Model
{
    protected $guarded = [];
    protected $casts = ['taken_at' => 'datetime'];

    public function case(): BelongsTo { return $this->belongsTo(DisciplineCase::class, 'discipline_case_id'); }
    public function actor(): BelongsTo { return $this->belongsTo(User::class, 'actor_id'); }
}
