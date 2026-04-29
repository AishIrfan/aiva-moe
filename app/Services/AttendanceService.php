<?php

namespace App\Services;

use App\Models\AttendanceSnapshot;
use App\Models\LeaveRequest;
use App\Models\School;
use App\Models\Student;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class AttendanceService
{
    /** Seed a day's attendance with 'present' if no record exists yet. */
    public function seedForDate(School $school, CarbonInterface $date): int
    {
        $dateStr = $date->toDateString();
        $students = Student::where('school_id', $school->id)->where('status', 'active')->get();
        $existing = AttendanceSnapshot::where('school_id', $school->id)
            ->where('date', $dateStr)
            ->pluck('student_id')->all();

        $created = 0;
        foreach ($students as $student) {
            if (in_array($student->id, $existing, true)) continue;
            AttendanceSnapshot::create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'school_class_id' => $student->activeEnrollment?->school_class_id,
                'date' => $dateStr,
                'status' => 'present',
                'source' => 'auto',
            ]);
            $created++;
        }
        return $created;
    }

    public function override(AttendanceSnapshot $row, array $data, ?int $userId): AttendanceSnapshot
    {
        $before = $row->only(['status', 'absent_reason_id', 'notes']);
        $row->update(array_merge($data, ['source' => 'manual', 'recorded_by' => $userId]));
        AuditLogger::log('attendance.override', $row, $before, $row->only(['status', 'absent_reason_id', 'notes']));
        return $row;
    }

    /** Apply an approved leave decision to attendance rows. */
    public function applyLeaveToAttendance(LeaveRequest $leave): int
    {
        if ($leave->status !== 'approved') return 0;
        $status = $leave->type === 'medical' ? 'mc' : 'leave';
        $count = 0;
        $period = Carbon::parse($leave->from_date)->daysUntil(Carbon::parse($leave->to_date));
        foreach ($period as $day) {
            $snap = AttendanceSnapshot::updateOrCreate(
                ['student_id' => $leave->student_id, 'date' => $day->toDateString()],
                [
                    'school_id' => $leave->school_id,
                    'status' => $status,
                    'source' => 'leave',
                    'notes' => $leave->reason,
                ]
            );
            $count++;
        }
        return $count;
    }
}
