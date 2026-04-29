<?php

namespace App\Http\Controllers\School;

use App\Models\AttendanceSnapshot;
use App\Models\Student;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends SchoolContextController
{
    public function __construct(private AttendanceService $service) {}

    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $date = Carbon::parse($request->get('date', today()->toDateString()));
        $this->service->seedForDate($school, $date);
        $rows = AttendanceSnapshot::where('school_id', $school->id)
            ->where('date', $date->toDateString())
            ->with(['student', 'schoolClass', 'absentReason'])
            ->orderBy('student_id')
            ->paginate(50)->withQueryString();
        return view('school.attendance', compact('school', 'date', 'rows'));
    }

    public function override(Request $request)
    {
        $data = $request->validate([
            'snapshot_id' => ['required', 'exists:attendance_snapshots,id'],
            'status' => ['required', 'in:present,absent,late,leave,mc'],
            'absent_reason_id' => ['nullable', 'exists:absent_reasons,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);
        $row = AttendanceSnapshot::findOrFail($data['snapshot_id']);
        $this->service->override($row, [
            'status' => $data['status'],
            'absent_reason_id' => $data['absent_reason_id'] ?? null,
            'notes' => $data['notes'] ?? null,
        ], $request->user()->id);
        return back()->with('status', 'Attendance updated.');
    }

    public function followUp(Request $request)
    {
        $school = $this->requireSchool($request);
        $rows = AttendanceSnapshot::where('school_id', $school->id)
            ->whereIn('status', ['absent', 'late'])
            ->whereDate('date', '>=', now()->subDays(7))
            ->with('student')->latest('date')->paginate(50);
        return view('school.attendance-follow-up', compact('school', 'rows'));
    }

    public function records(Request $request)
    {
        $school = $this->requireSchool($request);
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $rows = AttendanceSnapshot::where('school_id', $school->id)
            ->whereBetween('date', [$from, $to])
            ->with(['student'])
            ->orderBy('date', 'desc')->paginate(50)->withQueryString();
        return view('school.attendance-records', compact('school', 'rows', 'from', 'to'));
    }

    public function monthly(Request $request)
    {
        $school = $this->requireSchool($request);
        $month = $request->get('month', now()->format('Y-m'));
        $start = Carbon::parse($month.'-01');
        $rows = AttendanceSnapshot::where('school_id', $school->id)
            ->whereBetween('date', [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()])
            ->selectRaw('student_id, status, count(*) as n')
            ->groupBy('student_id', 'status')->get()->groupBy('student_id');
        $students = Student::where('school_id', $school->id)->get()->keyBy('id');
        return view('school.attendance-monthly-summary', compact('school', 'month', 'rows', 'students'));
    }

    public function warningLetters(Request $request)
    {
        $school = $this->requireSchool($request);
        $threshold = (int) $request->get('threshold', 3);
        $offenders = AttendanceSnapshot::where('school_id', $school->id)
            ->whereDate('date', '>=', now()->subDays(30))
            ->whereIn('status', ['absent', 'late'])
            ->selectRaw('student_id, count(*) as n')
            ->groupBy('student_id')->having('n', '>=', $threshold)
            ->orderByDesc('n')->get();
        $students = Student::whereIn('id', $offenders->pluck('student_id'))->get()->keyBy('id');
        return view('school.attendance-warning-letters', compact('school', 'offenders', 'students', 'threshold'));
    }
}
