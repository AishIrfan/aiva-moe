<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogbookEntry extends Model
{
    protected $guarded = [];
    protected $casts = [
        'submitted_at' => 'date',
        'reviewed_at'  => 'date',
    ];

    public function placement(): BelongsTo { return $this->belongsTo(Placement::class); }
    public function reviewer(): BelongsTo  { return $this->belongsTo(Pensyarah::class, 'reviewed_by_pensyarah_id'); }
}
