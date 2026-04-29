<?php

namespace App\Http\Controllers\School;

use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class GradesClassesController extends SchoolContextController
{
    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $grades = Grade::where('school_id', $school->id)->orderBy('level')->with('classes')->get();
        $teachers = Teacher::where('school_id', $school->id)->orderBy('name')->get();
        return view('school.grades-classes', compact('school', 'grades', 'teachers'));
    }

    public function storeGrade(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'level' => ['required', 'integer', 'min:0'],
        ]);
        $school = $this->requireSchool($request);
        $grade = Grade::create(array_merge($data, ['school_id' => $school->id]));
        AuditLogger::log('grade.create', $grade, [], $data);
        return back()->with('status', 'Grade added.');
    }

    public function updateGrade(Request $request, Grade $grade)
    {
        $this->ensureOwned($request, $grade);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'level' => ['required', 'integer', 'min:0'],
        ]);
        $before = $grade->only(array_keys($data));
        $grade->update($data);
        AuditLogger::log('grade.update', $grade, $before, $data);
        return back()->with('status', 'Grade updated.');
    }

    public function storeClass(Request $request)
    {
        $data = $request->validate([
            'grade_id' => ['required', 'exists:grades,id'],
            'name' => ['required', 'string', 'max:60'],
            'capacity' => ['required', 'integer', 'min:1', 'max:200'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);
        $school = $this->requireSchool($request);
        $class = SchoolClass::create(array_merge($data, ['school_id' => $school->id]));
        AuditLogger::log('class.create', $class, [], $data);
        return back()->with('status', 'Class created.');
    }

    public function updateClass(Request $request, SchoolClass $class)
    {
        $this->ensureOwned($request, $class);
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:60'],
            'capacity' => ['sometimes', 'required', 'integer', 'min:1', 'max:200'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);
        $before = $class->only(array_keys($data));
        $class->update($data);
        AuditLogger::log('class.update', $class, $before, $data);
        return back()->with('status', 'Class updated.');
    }
}
