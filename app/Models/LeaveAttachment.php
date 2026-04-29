<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveAttachment extends Model
{
    protected $guarded = [];
    public function submission(): BelongsTo { return $this->belongsTo(LeaveSubmission::class, 'leave_submission_id'); }
}
