<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsentReason extends Model
{
    protected $guarded = [];
    protected $casts = [
        'counts_as_present' => 'boolean',
        'is_excused' => 'boolean',
    ];
}
