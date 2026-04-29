<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisciplineCase extends Model
{
    protected $guarded = [];
    protected $casts = [
        'incident_date' => 'date',
        'repeat_of' => 'array',
    ];

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function reporter(): BelongsTo { return $this->belongsTo(User::class, 'reported_by'); }
    public function actions(): HasMany { return $this->hasMany(DisciplineAction::class); }
    public function evidence(): HasMany { return $this->hasMany(DisciplineEvidence::class); }
}
