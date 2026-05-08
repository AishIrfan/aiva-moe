<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_MOE_ADMIN     = 'moe_admin';
    public const ROLE_MOE_VIEWER    = 'moe_viewer';
    public const ROLE_SCHOOL_ADMIN  = 'school_admin';
    public const ROLE_IPG_ADMIN     = 'ipg_admin';
    public const ROLE_BPG_ADMIN     = 'bpg_admin';
    public const ROLE_KETUA_JABATAN = 'ketua_jabatan';
    public const ROLE_PENSYARAH     = 'pensyarah';
    public const ROLE_TRAINEE       = 'trainee';
    public const ROLE_TEACHER       = 'teacher';
    public const ROLE_OPERATOR      = 'operator';

    /** Roles that live inside IPG mode. Used by sidebar + middleware. */
    public const IPG_ROLES = [
        self::ROLE_IPG_ADMIN,
        self::ROLE_BPG_ADMIN,
        self::ROLE_KETUA_JABATAN,
        self::ROLE_PENSYARAH,
        self::ROLE_TRAINEE,
    ];

    protected $fillable = [
        'name', 'email', 'password',
        'role', 'mode', 'school_id', 'campus_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function campus(): BelongsTo { return $this->belongsTo(Campus::class); }
    public function trainee(): HasOne   { return $this->hasOne(Trainee::class); }
    public function pensyarah(): HasOne { return $this->hasOne(Pensyarah::class); }

    public function isMoe(): bool          { return $this->role === self::ROLE_MOE_ADMIN; }
    public function isMoeViewer(): bool    { return $this->role === self::ROLE_MOE_VIEWER; }
    public function isSchoolAdmin(): bool  { return $this->role === self::ROLE_SCHOOL_ADMIN; }
    public function isIpg(): bool          { return $this->role === self::ROLE_IPG_ADMIN; }
    public function isBpg(): bool          { return $this->role === self::ROLE_BPG_ADMIN; }
    public function isKetuaJabatan(): bool { return $this->role === self::ROLE_KETUA_JABATAN; }
    public function isPensyarah(): bool    { return $this->role === self::ROLE_PENSYARAH; }
    public function isTrainee(): bool      { return $this->role === self::ROLE_TRAINEE; }

    /** Lives inside IPG mode (for nav + redirects). */
    public function livesInIpgMode(): bool
    {
        return in_array($this->role, self::IPG_ROLES, true);
    }

    /** Can flip between modes via the sidebar switcher (superadmin equivalent). */
    public function canSwitchMode(): bool { return $this->isMoe(); }

    /** Default mode this user lands in if no explicit session mode is set. */
    public function defaultMode(): string
    {
        if ($this->isMoe())               return 'moe';
        if ($this->isMoeViewer())         return 'moe';
        if ($this->livesInIpgMode())      return 'ipg';
        return 'school';
    }
}
