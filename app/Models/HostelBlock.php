<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostelBlock extends Model
{
    protected $guarded = [];
    protected $casts = ['meta' => 'array'];

    public function campus(): BelongsTo { return $this->belongsTo(Campus::class); }
    public function rooms(): HasMany    { return $this->hasMany(HostelRoom::class, 'block_id'); }
}
