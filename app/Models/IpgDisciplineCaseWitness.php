<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Witness on a discipline case. Either internal (witness_user_id set) or
 * external (witness_name set, witness_contact optional). Service-layer
 * validation enforces "at least one of witness_user_id or witness_name is
 * set" — witness_contact alone is insufficient to identify a person.
 */
class IpgDisciplineCaseWitness extends Model
{
    protected $guarded = [];

    public function case(): BelongsTo
    {
        return $this->belongsTo(IpgDisciplineCase::class, 'ipg_discipline_case_id');
    }

    public function witness(): BelongsTo
    {
        return $this->belongsTo(User::class, 'witness_user_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function isInternal(): bool { return $this->witness_user_id !== null; }
    public function isExternal(): bool { return $this->witness_user_id === null && $this->witness_name !== null; }
}
