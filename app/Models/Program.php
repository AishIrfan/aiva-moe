<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    /** Canonical PISMP code — used in seeders and helpers. */
    public const PISMP = 'PISMP';

    public function cohorts(): HasMany { return $this->hasMany(Cohort::class); }
}
