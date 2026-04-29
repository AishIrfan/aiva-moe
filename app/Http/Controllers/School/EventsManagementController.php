<?php

namespace App\Http\Controllers\School;

use App\Models\EventApproval;
use App\Models\EventAttendance;
use App\Models\EventParticipant;
use App\Models\ManagementEvent;
use App\Services\AuditLogger;
use App\Services\EventManagementService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventsManagementController extends SchoolContextController
{
    public function __construct(private EventManagementService $service) {}

    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $events = ManagementEvent::where('school_id', $school->id)
            ->with(['organizer', 'participants'])
            ->latest()->paginate(25);
        $active = $request->get('event_id') ? ManagementEvent::with(['participants.student', 'letters', 'attendance.student', 'approvals'])->find($request->get('event_id')) : null;
        return view('school.events-management', compact('school', 'events', 'active'));
    }

    public function store(Request $request)
    {
        $school = $this->requireSchool($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);
        $event = ManagementEvent::create(array_merge($data, [
            'school_id' => $school->id,
            'organizer_id' => $request->user()->id,
            'status' => 'draft',
        ]));
        AuditLogger::log('event.create', $event, [], $data);
        return back()->with('status', 'Event created.');
    }

    public function update(Request $request, ManagementEvent $event)
    {
        $this->ensureOwned($request, $event);
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['sometimes', 'date'],
        ]);
        $before = $event->only(array_keys($data));
        $event->update($data);
        AuditLogger::log('event.update', $event, $before, $data);
        return back()->with('status', 'Event updated.');
    }

    public function transition(Request $request, ManagementEvent $event)
    {
        $this->ensureOwned($request, $event);
        $data = $request->validate(['status' => ['required', 'in:'.implode(',', EventManagementService::STATES)]]);
        try {
            $this->service->transition($event, $data['status']);
            AuditLogger::log('event.transition', $event, [], $data);
            return back()->with('status', 'Status changed.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }
    }

    public function addParticipant(Request $request, ManagementEvent $event)
    {
        $this->ensureOwned($request, $event);
        $data = $request->validate([
            'student_id' => ['nullable', 'exists:students,id'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'role' => ['nullable', 'in:participant,lead,helper'],
        ]);
        EventParticipant::updateOrCreate(
            ['management_event_id' => $event->id, 'student_id' => $data['student_id'] ?? null],
            array_merge($data, ['management_event_id' => $event->id])
        );
        return back()->with('status', 'Participant added.');
    }

    public function markAttendance(Request $request, ManagementEvent $event)
    {
        $this->ensureOwned($request, $event);
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'status' => ['required', 'in:present,absent,late'],
        ]);
        EventAttendance::updateOrCreate(
            ['management_event_id' => $event->id, 'student_id' => $data['student_id']],
            ['status' => $data['status'], 'checked_in_at' => now()]
        );
        return back()->with('status', 'Attendance recorded.');
    }

    public function approve(Request $request, ManagementEvent $event)
    {
        $this->ensureOwned($request, $event);
        $data = $request->validate([
            'level' => ['required', 'in:hod,principal,district'],
            'decision' => ['required', 'in:approved,rejected,returned'],
            'note' => ['nullable', 'string'],
        ]);
        EventApproval::create(array_merge($data, [
            'management_event_id' => $event->id,
            'approver_id' => $request->user()->id,
            'decided_at' => now(),
        ]));
        return back()->with('status', 'Approval recorded.');
    }

    public function export(Request $request): StreamedResponse
    {
        $school = $this->requireSchool($request);
        $events = ManagementEvent::where('school_id', $school->id)->get();
        return response()->streamDownload(function () use ($events) {
            $fh = fopen('php://output', 'w');
            fputcsv($fh, ['ID', 'Title', 'Status', 'Starts', 'Ends']);
            foreach ($events as $e) fputcsv($fh, [$e->id, $e->title, $e->status, $e->starts_at, $e->ends_at]);
            fclose($fh);
        }, 'events-'.now()->format('Ymd').'.csv');
    }
}
