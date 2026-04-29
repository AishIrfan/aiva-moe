<?php

namespace Tests\Unit;

use App\Services\DisciplineService;
use App\Services\EventManagementService;
use App\Services\LeaveSubmissionService;
use PHPUnit\Framework\TestCase;

class ServicesTest extends TestCase
{
    public function test_leave_day_count_inclusive(): void
    {
        $this->assertEquals(3, (new LeaveSubmissionService)->dayCount('2026-04-01', '2026-04-03'));
    }

    public function test_event_transitions_valid(): void
    {
        $this->assertArrayHasKey('draft', EventManagementService::TRANSITIONS);
        $this->assertContains('pending_approval', EventManagementService::TRANSITIONS['draft']);
    }

    public function test_discipline_transitions_valid(): void
    {
        $this->assertArrayHasKey('submitted', DisciplineService::TRANSITIONS);
        $this->assertContains('pending_review', DisciplineService::TRANSITIONS['submitted']);
    }
}
