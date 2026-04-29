<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    use ApiResponder;

    public function index(Request $request)
    {
        $q = Student::query();
        if ($sid = $request->get('school_id')) $q->where('school_id', $sid);
        if ($search = $request->get('q')) $q->where('name', 'like', "%{$search}%");
        return $this->ok($q->limit(500)->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'name' => ['required', 'string'],
            'student_number' => ['nullable', 'string', 'unique:students,student_number'],
            'gender' => ['nullable', 'string'],
            'date_of_birth' => ['nullable', 'date'],
        ]);
        return $this->ok(Student::create($data));
    }

    public function show(Student $student) { return $this->ok($student->load('guardians', 'activeEnrollment.schoolClass')); }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string'],
            'status' => ['sometimes', 'in:active,transferred,graduated'],
        ]);
        $student->update($data);
        return $this->ok($student);
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return $this->ok(null);
    }
}
