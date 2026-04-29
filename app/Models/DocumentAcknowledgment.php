<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAcknowledgment extends Model
{
    protected $guarded = [];
    protected $casts = ['acknowledged_at' => 'datetime'];

    public function document(): BelongsTo { return $this->belongsTo(Document::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function guardian(): BelongsTo { return $this->belongsTo(Guardian::class); }
}
