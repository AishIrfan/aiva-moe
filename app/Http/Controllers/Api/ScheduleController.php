<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    use ApiResponder;

    public function index(Request $request)
    {
        $q = Schedule::with(['schoolClass.grade', 'teacher', 'subject', 'period']);
        if ($sid = $request->get('school_id')) $q->where('school_id', $sid);
        if ($cid = $request->get('school_class_id')) $q->where('school_class_id', $cid);
        return $this->ok($q->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'period_id' => ['required', 'exists:periods,id'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'room' => ['nullable', 'string'],
        ]);
        return $this->ok(Schedule::create($data));
    }

    public function show(Schedule $schedule) { return $this->ok($schedule); }
    public function update(Request $request, Schedule $schedule) { $schedule->update($request->all()); return $this->ok($schedule); }
    public function destroy(Schedule $schedule) { $schedule->delete(); return $this->ok(null); }
}
