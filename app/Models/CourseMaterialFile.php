<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Single uploaded file attached to a CourseMaterial. Disk + path identify the
 * stored object; mime_type is set from MIME-sniffing at upload time (NOT from
 * extension). Replacement uses a `replaced_at` flag rather than deleting the
 * row so audit trail is preserved.
 */
class CourseMaterialFile extends Model
{
    protected $guarded = [];
    protected $casts   = [
        'size_bytes'  => 'integer',
        'sort_order'  => 'integer',
        'replaced_at' => 'datetime',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(CourseMaterial::class, 'course_material_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function isActive(): bool
    {
        return $this->replaced_at === null;
    }
}
