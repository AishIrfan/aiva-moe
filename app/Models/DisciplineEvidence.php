<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplineEvidence extends Model
{
    protected $guarded = [];
    public function case(): BelongsTo { return $this->belongsTo(DisciplineCase::class, 'discipline_case_id'); }
}
