<?php

namespace App\Http\Controllers\School;

use App\Models\AssistanceApplication;
use App\Models\AttendanceSnapshot;
use App\Models\DisciplineCase;
use App\Models\DocumentAcknowledgment;
use App\Models\Event;
use App\Models\LeaveRequest;
use App\Models\Student;
use App\Models\StudentNote;
use App\Services\AuditLogger;
use App\Services\PracticumProjection;
use Illuminate\Http\Request;

class StudentsController extends SchoolContextController
{
    public function index(Request $request, PracticumProjection $practicum)
    {
        $school = $this->requireSchool($request);
        $q = Student::where('school_id', $school->id)->with('activeEnrollment.schoolClass.grade');
        if ($search = $request->get('q')) {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%")
                  ->orWhere('ic_number', 'like', "%{$search}%");
            });
        }
        $students = $q->orderBy('name')->paginate(50)->withQueryString();

        // §6.1 cross-mode projection: surface IPG trainees on placement at this school.
        $placedTrainees = $practicum->activeForSchool($school->id);

        return view('school.students', compact('school', 'students', 'placedTrainees'));
    }

    public function student360(Request $request)
    {
        $school = $this->requireSchool($request);

        // Direct navigation to /school/student-360 with no query param used to
        // throw ModelNotFoundException → 404 (findOrFail(0)). Redirect to the
        // students list instead — that's the natural "pick a student first"
        // entry point for this drill-down view.
        $studentId = (int) $request->get('student_id');
        if (! $studentId) {
            return redirect()->route('school.students')
                ->with('status', 'Pick a student to view their 360° profile.');
        }

        $student = Student::where('school_id', $school->id)->find($studentId);
        if (! $student) {
            return redirect()->route('school.students')
                ->with('status', 'That student record was not found on this school.');
        }

        $student->load(['guardians', 'activeEnrollment.schoolClass.grade', 'notes.author', 'events', 'leaveRequests', 'disciplineCases', 'assistanceApplications.program']);
        $recentAttendance = AttendanceSnapshot::where('student_id', $student->id)
            ->orderBy('date', 'desc')->limit(30)->get();
        $acks = DocumentAcknowledgment::where('student_id', $student->id)->with('document')->get();
        return view('school.student-360', compact('student', 'recentAttendance', 'acks'));
    }

    public function addNote(Request $request, Student $student)
    {
        $this->ensureOwned($request, $student);
        $data = $request->validate([
            'category' => ['required', 'in:general,academic,behavioral,medical'],
            'body' => ['required', 'string', 'max:2000'],
        ]);
        $note = StudentNote::create(array_merge($data, [
            'student_id' => $student->id,
            'author_id' => $request->user()->id,
        ]));
        AuditLogger::log('student.note.create', $note, [], $data);
        return back()->with('status', 'Note added.');
    }

    public function addIncident(Request $request, Student $student)
    {
        $this->ensureOwned($request, $student);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'severity' => ['required', 'in:info,warn,critical'],
        ]);
        $event = Event::create(array_merge($data, [
            'school_id' => $student->school_id,
            'student_id' => $student->id,
            'type' => 'manual',
            'status' => 'open',
            'detected_at' => now(),
        ]));
        AuditLogger::log('student.incident.create', $event, [], $data);
        return back()->with('status', 'Incident recorded for student.');
    }
}
