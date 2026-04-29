<?php

namespace App\Services;

use App\Models\ManagementEvent;

class EventManagementService
{
    public const STATES = ['draft', 'pending_approval', 'approved', 'ongoing', 'completed', 'cancelled', 'rejected', 'returned_for_revision'];

    public const TRANSITIONS = [
        'draft' => ['pending_approval', 'cancelled'],
        'pending_approval' => ['approved', 'rejected', 'returned_for_revision'],
        'returned_for_revision' => ['pending_approval', 'cancelled'],
        'approved' => ['ongoing', 'cancelled'],
        'ongoing' => ['completed', 'cancelled'],
    ];

    public function canTransition(ManagementEvent $event, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$event->status] ?? [], true);
    }

    public function transition(ManagementEvent $event, string $to): ManagementEvent
    {
        if (! $this->canTransition($event, $to)) {
            throw new \InvalidArgumentException("Invalid transition: {$event->status} → {$to}");
        }
        $event->update(['status' => $to]);
        return $event;
    }

    public function overlaps(ManagementEvent $event): bool
    {
        return ManagementEvent::where('school_id', $event->school_id)
            ->where('id', '!=', $event->id ?? 0)
            ->whereIn('status', ['approved', 'ongoing'])
            ->where(function ($q) use ($event) {
                $q->whereBetween('starts_at', [$event->starts_at, $event->ends_at])
                  ->orWhereBetween('ends_at', [$event->starts_at, $event->ends_at]);
            })->exists();
    }
}
