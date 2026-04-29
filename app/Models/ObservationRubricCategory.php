<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservationRubricCategory extends Model
{
    protected $guarded = [];

    public function rubric(): BelongsTo
    {
        return $this->belongsTo(ObservationRubric::class, 'observation_rubric_id');
    }
}
