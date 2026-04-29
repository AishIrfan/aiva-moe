<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CocurricularActivity extends Model
{
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    public function campus(): BelongsTo { return $this->belongsTo(Campus::class); }
    public function participations(): HasMany { return $this->hasMany(CocurricularParticipation::class, 'activity_id'); }
}
