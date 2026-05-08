<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * School-mode classroom video recording.
 *
 * Authorization tiers (per CLASS_RECORDING_CHECKLIST.md §7) — enforced in
 * `ClassRecordingController`, NOT here:
 *   - MOE-tier (moe_admin / moe_viewer): all schools, view + download + preserve
 *   - School Admin: their school only, view + download + preserve
 *   - Teacher: own recordings only, view; cannot download or preserve
 *   - Other roles: no access
 *
 * Storage: rows know which `file_disk` they live on (column, not config) so
 * cross-disk migrations work. Path scheme uses `file_uuid` not the integer
 * `id` so URLs aren't enumerable.
 */
class ClassRecording extends Model
{
    use SoftDeletes;

    public const STATUS_RECORDING  = 'recording';
    public const STATUS_UPLOADING  = 'uploading';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_READY      = 'ready';
    public const STATUS_FAILED     = 'failed';
    public const STATUS_ARCHIVED   = 'archived';

    public const STATUSES = [
        self::STATUS_RECORDING,
        self::STATUS_UPLOADING,
        self::STATUS_PROCESSING,
        self::STATUS_READY,
        self::STATUS_FAILED,
        self::STATUS_ARCHIVED,
    ];

    public const CREATED_VIA_MANUAL     = 'manual_upload';
    public const CREATED_VIA_SMARTBOARD = 'smartboard_upload';

    /** Per-school setting keys (stored on the `settings` key-value table). */
    public const SETTING_ENABLED          = 'class_recording_enabled';
    public const SETTING_AUDIO_ENABLED    = 'class_recording_audio_enabled';
    public const SETTING_RETENTION_DAYS   = 'class_recording_retention_days';
    public const SETTING_MAX_FILE_SIZE_MB = 'class_recording_max_file_size_mb';
    public const SETTING_ALLOWED_MIMES    = 'class_recording_allowed_mime_types';

    public const DEFAULT_RETENTION_DAYS = 30;
    public const DEFAULT_MAX_SIZE_MB    = 2048; // 2 GB
    public const DEFAULT_ALLOWED_MIMES  = ['video/mp4'];
    public const RETENTION_MIN_DAYS     = 7;
    public const RETENTION_MAX_DAYS     = 365;

    protected $guarded = [];

    protected $casts = [
        'started_at'           => 'datetime',
        'ended_at'             => 'datetime',
        'duration_seconds'     => 'integer',
        'file_size_bytes'      => 'integer',
        'preserved'            => 'boolean',
        'preserved_at'         => 'datetime',
        'retention_expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $r) {
            if (empty($r->file_uuid)) {
                $r->file_uuid = (string) Str::uuid();
            }
        });
    }

    // -------- Relations --------

    public function school(): BelongsTo      { return $this->belongsTo(School::class); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class); }
    public function teacher(): BelongsTo     { return $this->belongsTo(User::class, 'teacher_user_id'); }
    public function preservedBy(): BelongsTo { return $this->belongsTo(User::class, 'preserved_by_user_id'); }
    public function createdBy(): BelongsTo   { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function referencedBy(): MorphTo  { return $this->morphTo('referenced_by'); }

    /** Audit-log entries pointing at this recording (auditable polymorph). */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    // -------- State predicates --------

    public function isPlayable(): bool
    {
        return $this->status === self::STATUS_READY && ! $this->trashed();
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    public function isPreserved(): bool
    {
        return (bool) $this->preserved;
    }

    public function isPastRetention(?Carbon $now = null): bool
    {
        $now ??= now();
        return $this->retention_expires_at !== null && $this->retention_expires_at->lt($now);
    }

    /** True iff this recording is a candidate for the daily retention pruner. */
    public function isPrunable(?Carbon $now = null): bool
    {
        return ! $this->isPreserved()
            && ! $this->isArchived()
            && $this->isPastRetention($now);
    }

    // -------- Settings helpers --------

    /**
     * Path scheme: class_recordings/{school_id}/{Y}/{m}/{file_uuid}.{ext}
     * Uses the recording's started_at (NOT now()) so re-running with the same
     * row reproduces the same path — important for the seeder.
     */
    public static function buildStoragePath(int $schoolId, string $fileUuid, ?Carbon $when = null, string $extension = 'mp4'): string
    {
        $when ??= now();
        return sprintf(
            'class_recordings/%d/%s/%s/%s.%s',
            $schoolId,
            $when->format('Y'),
            $when->format('m'),
            $fileUuid,
            ltrim($extension, '.')
        );
    }

    /** Default values for every per-school class-recording setting. */
    public static function defaultSettings(): array
    {
        return [
            self::SETTING_ENABLED          => false,
            self::SETTING_AUDIO_ENABLED    => false,
            self::SETTING_RETENTION_DAYS   => self::DEFAULT_RETENTION_DAYS,
            self::SETTING_MAX_FILE_SIZE_MB => self::DEFAULT_MAX_SIZE_MB,
            self::SETTING_ALLOWED_MIMES    => self::DEFAULT_ALLOWED_MIMES,
        ];
    }

    /**
     * Resolve all class-recording settings for a school, with defaults applied
     * for any unset key. Returns an associative array keyed by SETTING_* keys.
     */
    public static function settingsForSchool(?int $schoolId): array
    {
        $defaults = self::defaultSettings();
        if (! $schoolId) {
            return $defaults;
        }
        $out = [];
        foreach ($defaults as $key => $default) {
            $out[$key] = Setting::schoolValue($schoolId, $key, $default);
        }
        // Cast types defensively — settings.value comes back as JSON which
        // may have been re-typed during storage (e.g. ints stored as strings).
        $out[self::SETTING_ENABLED]          = (bool) $out[self::SETTING_ENABLED];
        $out[self::SETTING_AUDIO_ENABLED]    = (bool) $out[self::SETTING_AUDIO_ENABLED];
        $out[self::SETTING_RETENTION_DAYS]   = (int)  $out[self::SETTING_RETENTION_DAYS];
        $out[self::SETTING_MAX_FILE_SIZE_MB] = (int)  $out[self::SETTING_MAX_FILE_SIZE_MB];
        if (! is_array($out[self::SETTING_ALLOWED_MIMES])) {
            $out[self::SETTING_ALLOWED_MIMES] = self::DEFAULT_ALLOWED_MIMES;
        }
        return $out;
    }

    public static function isEnabledForSchool(?int $schoolId): bool
    {
        return (bool) Setting::schoolValue($schoolId, self::SETTING_ENABLED, false);
    }

    public static function isAudioEnabledForSchool(?int $schoolId): bool
    {
        return (bool) Setting::schoolValue($schoolId, self::SETTING_AUDIO_ENABLED, false);
    }
}
