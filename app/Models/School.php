<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $guarded = [];
    protected $casts = ['meta' => 'array'];

    public function grades(): HasMany { return $this->hasMany(Grade::class); }
    public function classes(): HasMany { return $this->hasMany(SchoolClass::class); }
    public function teachers(): HasMany { return $this->hasMany(Teacher::class); }
    public function students(): HasMany { return $this->hasMany(Student::class); }
    public function cameras(): HasMany { return $this->hasMany(Camera::class); }
    public function zones(): HasMany { return $this->hasMany(Zone::class); }
    public function events(): HasMany { return $this->hasMany(Event::class); }
    public function terms(): HasMany { return $this->hasMany(Term::class); }
}
