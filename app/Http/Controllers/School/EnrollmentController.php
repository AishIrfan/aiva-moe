<?php

namespace App\Http\Controllers\School;

use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class EnrollmentController extends SchoolContextController
{
    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $students = Student::where('school_id', $school->id)
            ->with(['activeEnrollment.schoolClass.grade'])
            ->orderBy('name')->paginate(50)->withQueryString();
        $classes = SchoolClass::where('school_id', $school->id)->with('grade')->get();
        return view('school.enrollment', compact('school', 'students', 'classes'));
    }

    public function assign(Request $request)
    {
        $school = $this->requireSchool($request);
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id,school_id,'.$school->id],
            'school_class_id' => ['required', 'exists:school_classes,id,school_id,'.$school->id],
        ]);
        $this->assignStudent((int) $data['student_id'], (int) $data['school_class_id']);
        return back()->with('status', 'Student assigned.');
    }

    public function transfer(Request $request)
    {
        $school = $this->requireSchool($request);
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id,school_id,'.$school->id],
            'school_class_id' => ['required', 'exists:school_classes,id,school_id,'.$school->id],
            'reason' => ['required', 'string', 'max:500'],
        ]);
        $this->assignStudent((int) $data['student_id'], (int) $data['school_class_id'], $data['reason']);
        return back()->with('status', 'Student transferred.');
    }

    private function assignStudent(int $studentId, int $classId, ?string $reason = null): void
    {
        Enrollment::where('student_id', $studentId)->where('is_active', true)->update([
            'is_active' => false, 'end_date' => now()->toDateString(),
        ]);
        $new = Enrollment::create([
            'student_id' => $studentId,
            'school_class_id' => $classId,
            'start_date' => now()->toDateString(),
            'is_active' => true,
            'reason' => $reason,
        ]);
        AuditLogger::log('enrollment.assign', $new, [], ['reason' => $reason]);
    }
}
