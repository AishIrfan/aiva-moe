<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Lookup table for discipline case categories. IPG Admin-configurable.
 * Deactivation via `is_active=false` (hides from new-case picker while
 * preserving historical references). Hard delete is restricted at the FK
 * level to preserve audit trail.
 */
class DisciplineCategory extends Model
{
    protected $guarded = [];
    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function cases(): HasMany
    {
        return $this->hasMany(IpgDisciplineCase::class);
    }
}
