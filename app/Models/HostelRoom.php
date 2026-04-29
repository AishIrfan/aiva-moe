<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostelRoom extends Model
{
    protected $guarded = [];

    public function block(): BelongsTo       { return $this->belongsTo(HostelBlock::class, 'block_id'); }
    public function assignments(): HasMany   { return $this->hasMany(HostelAssignment::class, 'room_id'); }
}
