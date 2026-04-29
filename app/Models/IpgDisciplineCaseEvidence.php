<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Evidence file attached to a discipline case. Stored on the project's
 * `local` disk (storage/app/private — outside the public web root) per the
 * upload security directive. This is the most sensitive file type in the
 * system: downloads MUST be auth-gated to authorized users only.
 *
 * MIME type is sniffed at upload time, NOT inferred from extension.
 */
class IpgDisciplineCaseEvidence extends Model
{
    protected $guarded = [];
    protected $casts   = ['size_bytes' => 'integer'];

    public function case(): BelongsTo
    {
        return $this->belongsTo(IpgDisciplineCase::class, 'ipg_discipline_case_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
