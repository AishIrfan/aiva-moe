<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Underlying disciplinary incident. Multiple discipline cases may reference
 * the same incident when more than one Pensyarah filed a report on it (W1.5
 * edge case). Standalone single-report cases have incident_id=NULL and never
 * touch this table.
 *
 * IPG Admin's `notes` field captures consolidated notes after reviewing all
 * linked cases together.
 */
class DisciplineIncident extends Model
{
    protected $guarded = [];
    protected $casts = ['occurred_at' => 'datetime'];

    public function cases(): HasMany
    {
        return $this->hasMany(IpgDisciplineCase::class, 'incident_id');
    }

    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function updatedBy(): BelongsTo { return $this->belongsTo(User::class, 'updated_by_user_id'); }
}
