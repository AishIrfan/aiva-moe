<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssistanceApplication extends Model
{
    protected $guarded = [];
    protected $casts = [
        'household_data' => 'array',
        'verified_at' => 'datetime',
        'decided_at' => 'datetime',
        'disbursed_at' => 'datetime',
        'requested_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
    ];

    public function program(): BelongsTo { return $this->belongsTo(AssistanceProgram::class, 'assistance_program_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function submitter(): BelongsTo { return $this->belongsTo(User::class, 'submitted_by'); }
    public function verifier(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }
    public function decider(): BelongsTo { return $this->belongsTo(User::class, 'decided_by'); }
}
