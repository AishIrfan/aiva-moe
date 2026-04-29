<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $guarded = [];
    protected $casts = ['value' => 'array'];

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public static function schoolValue(?int $schoolId, string $key, mixed $default = null): mixed
    {
        $row = static::where('scope', 'school')->where('school_id', $schoolId)->where('key', $key)->first();
        return $row?->value ?? $default;
    }

    public static function putSchoolValue(?int $schoolId, string $key, mixed $value): self
    {
        return static::updateOrCreate(
            ['scope' => 'school', 'school_id' => $schoolId, 'user_id' => null, 'key' => $key],
            ['value' => $value]
        );
    }
}
