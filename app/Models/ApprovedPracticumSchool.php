<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Approved Practicum School — the cross-mode bridge.
 *
 * IPG Admin curates per-campus which School-mode schools are eligible to host
 * practicum trainees. Penyelaras Praktikum (W3.2) selects from this list.
 *
 * `school` is a hard FK into the School-mode `schools` table — IPG mode reads
 * the canonical school record, never duplicates identity.
 */
class ApprovedPracticumSchool extends Model
{
    protected $guarded = [];

    public function campus(): BelongsTo  { return $this->belongsTo(Campus::class); }
    public function school(): BelongsTo  { return $this->belongsTo(School::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function updatedBy(): BelongsTo { return $this->belongsTo(User::class, 'updated_by_user_id'); }
}
