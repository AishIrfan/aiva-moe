<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $guarded = [];
    protected $casts = ['requires_ack' => 'boolean'];

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function links(): HasMany { return $this->hasMany(DocumentLink::class); }
    public function acknowledgments(): HasMany { return $this->hasMany(DocumentAcknowledgment::class); }
}
