<?php

namespace App\Http\Controllers\School;

use App\Models\Event;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class AlertsController extends SchoolContextController
{
    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $q = Event::where('school_id', $school->id);
        if ($status = $request->get('status')) $q->where('status', $status);
        if ($severity = $request->get('severity')) $q->where('severity', $severity);
        $events = $q->latest()->paginate(25)->withQueryString();
        return view('school.alerts', compact('school', 'events'));
    }

    public function acknowledge(Request $request, Event $event) { $this->ensureOwned($request, $event); return $this->transition($event, 'acknowledged', $request); }
    public function escalate(Request $request, Event $event)    { $this->ensureOwned($request, $event); return $this->transition($event, 'escalated', $request, ['severity' => 'critical']); }
    public function close(Request $request, Event $event)       { $this->ensureOwned($request, $event); return $this->transition($event, 'closed', $request, ['resolved_at' => now()]); }

    public function assign(Request $request, Event $event)
    {
        $this->ensureOwned($request, $event);
        $data = $request->validate(['assigned_to' => ['required', 'string', 'max:120']]);
        $event->update($data);
        AuditLogger::log('event.assign', $event, [], $data);
        return back()->with('status', 'Assignment updated.');
    }

    private function transition(Event $event, string $status, Request $request, array $extras = [])
    {
        $before = ['status' => $event->status];
        $event->update(array_merge(['status' => $status], $extras));
        AuditLogger::log('event.'.$status, $event, $before, ['status' => $status]);
        return back()->with('status', "Event {$status}.");
    }
}
