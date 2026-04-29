<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Observation extends Model
{
    protected $guarded = [];
    protected $casts = ['observed_at' => 'date'];

    public function placement(): BelongsTo  { return $this->belongsTo(Placement::class); }
    public function evaluator(): BelongsTo  { return $this->belongsTo(Pensyarah::class, 'evaluated_by_pensyarah_id'); }
}
