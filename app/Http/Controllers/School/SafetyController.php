<?php

namespace App\Http\Controllers\School;

use App\Models\Broadcast;
use App\Models\Event;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class SafetyController extends SchoolContextController
{
    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $recentIncidents = Event::where('school_id', $school->id)
            ->where('type', 'incident')->latest()->limit(20)->get();
        $broadcasts = Broadcast::where('school_id', $school->id)->latest()->limit(20)->get();
        return view('school.safety', compact('school', 'recentIncidents', 'broadcasts'));
    }

    public function createIncident(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'severity' => ['required', 'in:info,warn,critical'],
        ]);
        $school = $this->requireSchool($request);
        $event = Event::create(array_merge($data, [
            'school_id' => $school->id,
            'type' => 'incident',
            'status' => 'open',
            'detected_at' => now(),
        ]));
        AuditLogger::log('safety.incident.create', $event, [], $data);
        return back()->with('status', 'Incident logged.');
    }

    public function broadcast(Request $request)
    {
        $data = $request->validate([
            'channel' => ['required', 'in:safety,parents,staff,general'],
            'title' => ['required', 'string', 'max:200'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);
        $school = $this->requireSchool($request);
        $broadcast = Broadcast::create(array_merge($data, [
            'school_id' => $school->id,
            'user_id' => $request->user()->id,
            'sent_at' => now(),
        ]));
        AuditLogger::log('safety.broadcast.send', $broadcast, [], $data);
        return back()->with('status', 'Broadcast sent.');
    }
}
