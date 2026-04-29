<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class AivaEventController extends Controller
{
    use ApiResponder;

    public function index(Request $request)
    {
        $q = Event::query();
        if ($sid = $request->get('school_id')) $q->where('school_id', $sid);
        if ($type = $request->get('type')) $q->where('type', $type);
        if ($since = $request->get('since')) $q->where('created_at', '>=', $since);
        return $this->ok($q->latest()->limit(500)->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'type' => ['required', 'string'],
            'severity' => ['nullable', 'in:info,warn,critical'],
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'camera_id' => ['nullable', 'exists:cameras,id'],
            'payload' => ['nullable', 'array'],
        ]);
        $data['detected_at'] = $data['detected_at'] ?? now();
        return $this->ok(Event::create($data));
    }

    public function show(Event $event) { return $this->ok($event); }
    public function update(Request $request, Event $event) { $event->update($request->all()); return $this->ok($event); }
    public function destroy(Event $event) { $event->delete(); return $this->ok(null); }
}
