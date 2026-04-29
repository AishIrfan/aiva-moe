<?php

namespace App\Http\Controllers\School;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TimetablesController extends SchoolContextController
{
    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $classId = $request->get('class_id');
        $teacherId = $request->get('teacher_id');

        $q = Schedule::where('school_id', $school->id)
            ->with(['schoolClass.grade', 'teacher', 'subject', 'period']);
        if ($classId) $q->where('school_class_id', $classId);
        if ($teacherId) $q->where('teacher_id', $teacherId);
        $schedules = $q->orderBy('day_of_week')->orderBy('period_id')->get();

        $classes = SchoolClass::where('school_id', $school->id)->with('grade')->get();
        $teachers = Teacher::where('school_id', $school->id)->get();
        return view('school.timetables', compact('school', 'schedules', 'classes', 'teachers', 'classId', 'teacherId'));
    }
}
