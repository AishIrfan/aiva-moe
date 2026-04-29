<?php

namespace App\Services;

use App\Models\DisciplineCase;

class DisciplineService
{
    public const TRANSITIONS = [
        'draft' => ['submitted', 'cancelled'],
        'submitted' => ['pending_review', 'cancelled'],
        'pending_review' => ['under_investigation', 'rejected', 'cancelled'],
        'under_investigation' => ['action_required', 'resolved', 'closed'],
        'action_required' => ['resolved', 'closed'],
        'resolved' => ['closed'],
    ];

    public function canTransition(DisciplineCase $c, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$c->status] ?? [], true);
    }

    public function repeatOffenses(int $studentId): int
    {
        return DisciplineCase::where('student_id', $studentId)
            ->whereNotIn('status', ['draft', 'cancelled', 'rejected'])->count();
    }
}
