<?php

namespace App\Http\Controllers\School;

use App\Models\Period;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use App\Services\AuditLogger;
use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ScheduleController extends SchoolContextController
{
    public function __construct(private ScheduleService $service) {}

    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $tab = $request->get('tab', 'master'); // master|class|teacher|room|replacement
        $q = Schedule::where('school_id', $school->id)->with(['schoolClass.grade', 'teacher', 'subject', 'period']);

        if ($v = $request->get('class_id')) $q->where('school_class_id', $v);
        if ($v = $request->get('teacher_id')) $q->where('teacher_id', $v);
        if ($v = $request->get('room')) $q->where('room', $v);
        if ($v = $request->get('day')) $q->where('day_of_week', $v);
        if ($tab === 'replacement') $q->where('kind', 'replacement');
        else $q->where('kind', 'regular');

        $schedules = $q->orderBy('day_of_week')->orderBy('period_id')->get();
        $stats = [
            'total' => Schedule::where('school_id', $school->id)->count(),
            'regular' => Schedule::where('school_id', $school->id)->where('kind', 'regular')->count(),
            'replacements' => Schedule::where('school_id', $school->id)->where('kind', 'replacement')->count(),
        ];

        return view('school.schedule', [
            'school' => $school,
            'tab' => $tab,
            'schedules' => $schedules,
            'stats' => $stats,
            'classes' => SchoolClass::where('school_id', $school->id)->with('grade')->get(),
            'teachers' => Teacher::where('school_id', $school->id)->get(),
            'subjects' => Subject::where('school_id', $school->id)->get(),
            'periods' => Period::where('school_id', $school->id)->orderBy('order')->get(),
            'terms' => Term::where('school_id', $school->id)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $school = $this->requireSchool($request);
        $data['school_id'] = $school->id;
        $errs = $this->service->validateEntry($data);
        if ($errs) return back()->withErrors(['schedule' => $errs]);
        $sched = Schedule::create($data);
        AuditLogger::log('schedule.create', $sched, [], $data);
        return back()->with('status', 'Schedule created.');
    }

    public function update(Request $request, Schedule $schedule)
    {
        $this->ensureOwned($request, $schedule);
        $data = $this->validated($request);
        $data['school_id'] = $schedule->school_id;
        $errs = $this->service->validateEntry($data, $schedule->id);
        if ($errs) return back()->withErrors(['schedule' => $errs]);
        $before = $schedule->only(array_keys($data));
        $schedule->update($data);
        AuditLogger::log('schedule.update', $schedule, $before, $data);
        return back()->with('status', 'Schedule updated.');
    }

    public function destroy(Request $request, Schedule $schedule)
    {
        $this->ensureOwned($request, $schedule);
        $before = $schedule->toArray();
        $schedule->delete();
        AuditLogger::log('schedule.delete', null, $before, []);
        return back()->with('status', 'Schedule deleted.');
    }

    public function storeReplacement(Request $request)
    {
        $school = $this->requireSchool($request);
        $data = $request->validate([
            'replaces_schedule_id' => ['required', 'exists:schedules,id,school_id,'.$school->id],
            'teacher_id' => ['required', 'exists:teachers,id,school_id,'.$school->id],
            'effective_date' => ['required', 'date'],
        ]);
        $base = Schedule::findOrFail($data['replaces_schedule_id']);
        $new = $base->replicate();
        $new->fill(array_merge($data, ['kind' => 'replacement']));
        $new->save();
        AuditLogger::log('schedule.replacement', $new, [], $data);
        return back()->with('status', 'Replacement scheduled.');
    }

    public function export(Request $request): StreamedResponse
    {
        $school = $this->requireSchool($request);
        $rows = Schedule::where('school_id', $school->id)->with(['schoolClass.grade', 'teacher', 'subject', 'period'])->get();
        $file = 'schedule-'.$school->id.'-'.now()->format('Ymd').'.csv';
        return response()->streamDownload(function () use ($rows) {
            $fh = fopen('php://output', 'w');
            fputcsv($fh, ['Day', 'Period', 'Class', 'Teacher', 'Subject', 'Room', 'Kind']);
            foreach ($rows as $r) {
                fputcsv($fh, [$r->day_of_week, $r->period?->label, $r->schoolClass?->name, $r->teacher?->name, $r->subject?->name, $r->room, $r->kind]);
            }
            fclose($fh);
        }, $file);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'term_id' => ['nullable', 'exists:terms,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'period_id' => ['required', 'exists:periods,id'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'room' => ['nullable', 'string', 'max:50'],
            'kind' => ['nullable', 'in:regular,replacement,relief'],
        ]);
    }
}
