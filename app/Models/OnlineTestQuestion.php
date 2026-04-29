<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnlineTestQuestion extends Model
{
    public const KIND_MCQ          = 'mcq';
    public const KIND_SHORT_ANSWER = 'short_answer';

    public const KINDS = [self::KIND_MCQ, self::KIND_SHORT_ANSWER];

    protected $guarded = [];
    protected $casts   = ['marks' => 'decimal:2'];

    public function assessment(): BelongsTo { return $this->belongsTo(Assessment::class); }
    public function options(): HasMany      { return $this->hasMany(OnlineTestQuestionOption::class); }

    public function isMcq(): bool         { return $this->kind === self::KIND_MCQ; }
    public function isShortAnswer(): bool { return $this->kind === self::KIND_SHORT_ANSWER; }
}
