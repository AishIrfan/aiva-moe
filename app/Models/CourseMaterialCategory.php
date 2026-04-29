<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseMaterialCategory extends Model
{
    protected $guarded = [];
    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function materials(): HasMany
    {
        return $this->hasMany(CourseMaterial::class);
    }
}
