<?php

namespace App\Services;

use App\Models\LeaveSubmission;
use Carbon\Carbon;

class LeaveSubmissionService
{
    public const TRANSITIONS = [
        'draft' => ['submitted', 'cancelled'],
        'submitted' => ['pending_review', 'cancelled'],
        'pending_review' => ['approved', 'rejected', 'returned_for_revision'],
        'returned_for_revision' => ['submitted', 'cancelled'],
    ];

    public function dayCount(string $from, string $to): int
    {
        return (int) Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;
    }

    public function hasOverlap(int $studentId, string $from, string $to, ?int $ignoreId = null): bool
    {
        $q = LeaveSubmission::where('student_id', $studentId)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->where(function ($w) use ($from, $to) {
                $w->whereBetween('from_date', [$from, $to])
                  ->orWhereBetween('to_date', [$from, $to]);
            });
        if ($ignoreId) $q->where('id', '!=', $ignoreId);
        return $q->exists();
    }

    public function canTransition(LeaveSubmission $s, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$s->status] ?? [], true);
    }
}
