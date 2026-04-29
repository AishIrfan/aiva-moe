<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineTestQuestionOption extends Model
{
    protected $guarded = [];
    protected $casts   = ['is_correct' => 'boolean'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(OnlineTestQuestion::class, 'online_test_question_id');
    }
}
