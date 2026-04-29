<?php

namespace App\Http\Controllers\School;

use App\Models\LeaveRequest;
use App\Models\Student;
use App\Services\AttendanceService;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class LeavesController extends SchoolContextController
{
    public function __construct(private AttendanceService $attendance) {}

    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $leaves = LeaveRequest::where('school_id', $school->id)->with('student')->latest()->paginate(50);
        $students = Student::where('school_id', $school->id)->orderBy('name')->get();
        return view('school.leaves', compact('school', 'leaves', 'students'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'type' => ['required', 'in:personal,medical,family'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);
        $school = $this->requireSchool($request);
        $leave = LeaveRequest::create(array_merge($data, [
            'school_id' => $school->id,
            'submitted_by' => $request->user()->id,
            'status' => 'pending',
        ]));
        AuditLogger::log('leave.create', $leave, [], $data);
        return back()->with('status', 'Leave submitted.');
    }

    public function decide(Request $request, LeaveRequest $leaveRequest)
    {
        $this->ensureOwned($request, $leaveRequest);
        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'decision_note' => ['nullable', 'string', 'max:1000'],
        ]);
        $leaveRequest->update([
            'status' => $data['decision'],
            'decided_by' => $request->user()->id,
            'decided_at' => now(),
            'decision_note' => $data['decision_note'] ?? null,
        ]);
        if ($data['decision'] === 'approved') {
            $this->attendance->applyLeaveToAttendance($leaveRequest);
        }
        AuditLogger::log('leave.decide', $leaveRequest, [], $data);
        return back()->with('status', 'Leave decision recorded.');
    }
}
