<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    use ApiResponder;

    public function index(Request $request)
    {
        $q = SchoolClass::with('grade');
        if ($sid = $request->get('school_id')) $q->where('school_id', $sid);
        if ($gid = $request->get('grade_id')) $q->where('grade_id', $gid);
        return $this->ok($q->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'grade_id' => ['required', 'exists:grades,id'],
            'name' => ['required', 'string'],
            'capacity' => ['required', 'integer', 'min:1'],
        ]);
        return $this->ok(SchoolClass::create($data));
    }

    public function show(SchoolClass $class) { return $this->ok($class->load('grade', 'activeEnrollments.student')); }
    public function update(Request $request, SchoolClass $class) { $class->update($request->only(['name', 'capacity', 'homeroom_teacher_id'])); return $this->ok($class); }
    public function destroy(SchoolClass $class) { $class->delete(); return $this->ok(null); }
}
