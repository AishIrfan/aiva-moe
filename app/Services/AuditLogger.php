<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Audit logger — single polymorphic store for both School and IPG modes.
 *
 * IPG audit convention (Wave 1):
 *   - Action strings for IPG-mode events MUST be namespaced with the prefix
 *     `ipg.` followed by `<entity>.<verb>` — e.g. `ipg.practicum_window.created`,
 *     `ipg.placement.confirmed`, `ipg.observation.submitted`,
 *     `ipg.placement_letter.dispatched`, `ipg.evaluation.submitted`.
 *   - For BPG-originated events (rubric/template publish): prefix with `bpg.`,
 *     e.g. `bpg.observation_rubric.published`.
 *   - School-mode actions remain unprefixed (preserves backwards compatibility).
 *
 * The `school_id` column is reused for both modes — for IPG entries pass the
 * campus_id as the third argument. Read paths that filter audit logs by mode
 * should match on the action prefix, not the school_id column.
 */
class AuditLogger
{
    /** Action prefix for IPG-originated entries. */
    public const IPG_PREFIX = 'ipg.';

    /** Action prefix for BPG-originated entries (ministry-level). */
    public const BPG_PREFIX = 'bpg.';

    public static function log(string $action, ?Model $model = null, array $before = [], array $after = [], ?int $schoolId = null): AuditLog
    {
        return AuditLog::create([
            'user_id'        => Auth::id(),
            'school_id'      => $schoolId ?? session('school_id'),
            'action'         => $action,
            'auditable_type' => $model ? get_class($model) : null,
            'auditable_id'   => $model?->getKey(),
            'before'         => $before ?: null,
            'after'          => $after ?: null,
            'ip_address'     => Request::ip(),
        ]);
    }

    /**
     * Convenience wrapper for IPG-mode events. Stores the campus_id in the
     * existing school_id column (reused — see class docblock) and prefixes
     * the action with `ipg.` automatically.
     */
    public static function logIpg(string $entityVerb, ?Model $model = null, array $before = [], array $after = [], ?int $campusId = null): AuditLog
    {
        return self::log(
            self::IPG_PREFIX . $entityVerb,
            $model,
            $before,
            $after,
            $campusId ?? session('campus_id')
        );
    }
}
