<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Placement extends Model
{
    /**
     * Status state machine per IPG_WORKFLOWS.md §W3.4:
     *
     *   placed                  -- created by W3.2, no letter dispatched yet
     *   pending_acknowledgement -- letter dispatched, awaiting principal acknowledgement
     *   confirmed               -- principal acknowledged; trainee tag becomes visible
     *                              in host school's School-mode view (per §6.1)
     *   active                  -- practicum window in progress
     *   completed               -- window closed, evaluation submitted
     *   withdrawn               -- trainee withdrew (W3.6 exception)
     *   cancelled               -- school declined OR placement removed before confirm
     */
    public const STATUS_PLACED                  = 'placed';
    public const STATUS_PENDING_ACKNOWLEDGEMENT  = 'pending_acknowledgement';
    public const STATUS_CONFIRMED                = 'confirmed';
    public const STATUS_ACTIVE                   = 'active';
    public const STATUS_COMPLETED                = 'completed';
    public const STATUS_WITHDRAWN                = 'withdrawn';
    public const STATUS_CANCELLED                = 'cancelled';

    /** Statuses that should project the trainee tag into the host school's School-mode view (§6.1). */
    public const VISIBLE_TO_HOST_SCHOOL = [self::STATUS_CONFIRMED, self::STATUS_ACTIVE];

    protected $guarded = [];
    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'subjects'   => 'array',
        'levels'     => 'array',
        'meta'       => 'array',
    ];

    public function trainee(): BelongsTo    { return $this->belongsTo(Trainee::class); }
    public function hostSchool(): BelongsTo { return $this->belongsTo(School::class, 'host_school_id'); }
    public function semester(): BelongsTo   { return $this->belongsTo(Semester::class); }
    public function window(): BelongsTo     { return $this->belongsTo(PracticumWindow::class, 'practicum_window_id'); }
    public function supervisor(): BelongsTo { return $this->belongsTo(Pensyarah::class, 'supervisor_pensyarah_id'); }

    public function observations(): HasMany    { return $this->hasMany(Observation::class); }
    public function evaluation(): HasOne       { return $this->hasOne(Evaluation::class); }
    public function logbookEntries(): HasMany  { return $this->hasMany(LogbookEntry::class); }
    public function letters(): HasMany         { return $this->hasMany(PlacementLetter::class); }

    /** Scope: placements active on the given date (inclusive). */
    public function scopeActiveOn(Builder $query, $date): Builder
    {
        $date = $date instanceof \DateTimeInterface ? $date : \Carbon\Carbon::parse($date);
        return $query->whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date);
    }

    /** Scope: limited to a Pensyarah's assigned trainees (unless coordinator/MOE). */
    public function scopeVisibleTo(Builder $query, ?\App\Models\User $user): Builder
    {
        if (! $user) return $query;

        // MOE/IPG admins and Penyelaras Praktikum see everything campus-wide.
        if ($user->isMoe() || $user->isIpg() || $user->isBpg()) return $query;

        $pensyarah = $user->pensyarah;
        if ($pensyarah?->is_practicum_coordinator) return $query;

        // Plain Pensyarah → only their assigned placements.
        if ($pensyarah) {
            return $query->where('supervisor_pensyarah_id', $pensyarah->id);
        }

        // Any other role with no Pensyarah profile → see nothing.
        return $query->whereRaw('1 = 0');
    }
}
