<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Placement Letter Template — BPG-managed, campus-pinned.
 *
 * Versioned, locked at campus level. Body uses `{placeholder}` tokens
 * (e.g. `{trainee_name}`, `{host_school_name}`). Rendering is performed by a
 * Wave 3 service (`PlacementLetterRenderer`) using simple str_replace —
 * deliberately not Blade compilation, to keep stored content non-executable.
 */
class PlacementLetterTemplate extends Model
{
    public const STATUS_DRAFT   = 'draft';
    public const STATUS_ACTIVE  = 'active';
    public const STATUS_RETIRED = 'retired';

    protected $guarded = [];
    protected $casts = [
        'applied_from'           => 'date',
        'applied_to'             => 'date',
        'available_placeholders' => 'array',
    ];

    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function updatedBy(): BelongsTo { return $this->belongsTo(User::class, 'updated_by_user_id'); }

    public function isActive(): bool { return $this->status === self::STATUS_ACTIVE; }

    /**
     * Render the template body by substituting `{placeholder}` tokens.
     * Wave 3 will call this from the dispatch flow.
     */
    public function render(array $values): string
    {
        $body = $this->body;
        foreach ($values as $key => $value) {
            $body = str_replace('{' . $key . '}', (string) $value, $body);
        }
        return $body;
    }
}
