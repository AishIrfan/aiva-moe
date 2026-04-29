<?php

namespace App\Services;

use App\Models\Placement;
use App\Models\PlacementLetter;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Cross-mode projection: surface IPG-side practicum data to a host school.
 *
 * Per IPG_MODE_CHECKLIST §6.1, the host school sees a minimal projection of
 * placements during the practicum window:
 *  - placed trainees appear with a "trainee" tag in the school's lists
 *  - the placement letter shows up in the school's documents area
 *
 * The canonical data lives in IPG-mode tables (`placements`, `placement_letters`).
 * This service is read-only — it does not duplicate identity into school-mode tables.
 */
class PracticumProjection
{
    /**
     * Placements at a school visible to its School-mode UI as of the given date.
     *
     * Status filter (per IPG_WORKFLOWS.md §W3.4): the trainee tag becomes
     * visible to the host school the moment principal acknowledges (status
     * `confirmed`) and stays visible while `active`. It is hidden for
     * `placed` / `pending_acknowledgement` (school hasn't agreed yet) and
     * for `completed` / `withdrawn` / `cancelled` (trainee is gone).
     *
     * We deliberately don't gate on start_date — a `confirmed` placement
     * starting next month should still be visible so the host school can
     * prepare. The `end_date >= today` guard simply protects against stale
     * `confirmed` rows that nobody transitioned to `completed`.
     */
    public function activeForSchool(int $schoolId, ?Carbon $date = null): Collection
    {
        $date ??= now()->startOfDay();

        return Placement::query()
            ->where('host_school_id', $schoolId)
            ->whereIn('status', Placement::VISIBLE_TO_HOST_SCHOOL)
            ->whereDate('end_date', '>=', $date)
            ->with(['trainee.cohort.program', 'trainee.campus', 'supervisor'])
            ->orderBy('start_date')
            ->get();
    }

    /**
     * Placement letters addressed to a host school for visible placements.
     * Used by the school's Documents area to surface incoming correspondence.
     */
    public function lettersForSchool(int $schoolId, ?Carbon $date = null): Collection
    {
        $date ??= now()->startOfDay();

        return PlacementLetter::query()
            ->whereHas('placement', function ($q) use ($schoolId, $date) {
                $q->where('host_school_id', $schoolId)
                  ->whereIn('status', Placement::VISIBLE_TO_HOST_SCHOOL)
                  ->whereDate('end_date', '>=', $date);
            })
            ->with(['placement.trainee.campus'])
            ->orderByDesc('sent_at')
            ->get();
    }
}
