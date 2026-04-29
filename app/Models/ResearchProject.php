<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchProject extends Model
{
    protected $guarded = [];
    protected $casts = [
        'started_at'   => 'date',
        'submitted_at' => 'date',
        'milestones'   => 'array',
    ];

    public function trainee(): BelongsTo    { return $this->belongsTo(Trainee::class); }
    public function supervisor(): BelongsTo { return $this->belongsTo(Pensyarah::class, 'supervisor_pensyarah_id'); }

    public function isSubmitted(): bool { return in_array($this->status, ['submitted', 'evaluated'], true); }
}
