<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $guarded = [];

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function homeroomClasses(): HasMany { return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id'); }
    public function schedules(): HasMany { return $this->hasMany(Schedule::class); }
}
