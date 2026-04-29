<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CocurricularParticipation extends Model
{
    protected $guarded = [];

    public function trainee(): BelongsTo  { return $this->belongsTo(Trainee::class); }
    public function activity(): BelongsTo { return $this->belongsTo(CocurricularActivity::class, 'activity_id'); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
}
