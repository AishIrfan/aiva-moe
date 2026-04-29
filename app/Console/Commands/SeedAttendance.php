<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Services\AttendanceService;
use Illuminate\Console\Command;

class SeedAttendance extends Command
{
    protected $signature = 'attendance:seed-today';
    protected $description = 'Ensure every active student has an attendance row for today (idempotent).';

    public function handle(AttendanceService $service): int
    {
        $total = 0;
        foreach (School::all() as $school) {
            $total += $service->seedForDate($school, now());
        }
        $this->info("Seeded {$total} attendance rows.");
        return self::SUCCESS;
    }
}
