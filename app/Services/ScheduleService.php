<?php

namespace App\Services;

use App\Models\Schedule;
use Illuminate\Support\Collection;

class ScheduleService
{
    /** Return IDs of schedules that conflict with the given entry (teacher / class / room). */
    public function collectConflictIds(array $entry, ?int $ignoreId = null): array
    {
        $q = Schedule::where('school_id', $entry['school_id'])
            ->where('day_of_week', $entry['day_of_week'])
            ->where('period_id', $entry['period_id']);
        if ($ignoreId) $q->where('id', '!=', $ignoreId);

        $clashes = $q->get();
        $ids = [];
        foreach ($clashes as $c) {
            if ($entry['teacher_id'] && $c->teacher_id === (int) $entry['teacher_id']) $ids[] = $c->id;
            if ($entry['school_class_id'] && $c->school_class_id === (int) $entry['school_class_id']) $ids[] = $c->id;
            if (!empty($entry['room']) && $c->room === $entry['room']) $ids[] = $c->id;
        }
        return array_values(array_unique($ids));
    }

    public function validateEntry(array $entry, ?int $ignoreId = null): array
    {
        $errors = [];
        if (empty($entry['school_class_id'])) $errors[] = 'Class required.';
        if (empty($entry['period_id'])) $errors[] = 'Period required.';
        if (empty($entry['day_of_week']) || $entry['day_of_week'] < 1 || $entry['day_of_week'] > 7) $errors[] = 'Invalid day.';
        $conflicts = $this->collectConflictIds($entry, $ignoreId);
        if ($conflicts) $errors[] = 'Conflicts with schedule IDs: '.implode(',', $conflicts);
        return $errors;
    }

    public function overlayForDate(Collection $baseSchedules, Collection $replacements, string $date): Collection
    {
        $byKey = [];
        foreach ($baseSchedules as $s) {
            $byKey[$s->day_of_week.'-'.$s->period_id.'-'.$s->school_class_id] = $s;
        }
        foreach ($replacements as $r) {
            if ($r->effective_date?->toDateString() === $date) {
                $byKey[$r->day_of_week.'-'.$r->period_id.'-'.$r->school_class_id] = $r;
            }
        }
        return collect(array_values($byKey));
    }
}
