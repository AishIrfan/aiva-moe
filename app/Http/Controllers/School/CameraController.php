<?php

namespace App\Http\Controllers\School;

use App\Models\Camera;
use App\Models\CameraConfig;
use App\Models\Zone;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class CameraController extends SchoolContextController
{
    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $cameras = Camera::where('school_id', $school->id)->with(['zone', 'config'])->get();
        $zones = Zone::where('school_id', $school->id)->get();
        return view('school.cameras', compact('school', 'cameras', 'zones'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'serial' => ['nullable', 'string', 'unique:cameras,serial'],
            'zone_id' => ['nullable', 'exists:zones,id'],
            'stream_url' => ['nullable', 'url'],
        ]);
        $school = $this->requireSchool($request);
        $camera = Camera::create(array_merge($data, ['school_id' => $school->id]));
        CameraConfig::create(['camera_id' => $camera->id, 'thresholds' => [], 'retention_days' => 30, 'settings' => []]);
        AuditLogger::log('camera.create', $camera, [], $data);
        return back()->with('status', 'Camera added.');
    }

    public function update(Request $request, Camera $camera)
    {
        $this->ensureOwned($request, $camera);
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string'],
            'zone_id' => ['nullable', 'exists:zones,id'],
            'stream_url' => ['nullable', 'url'],
        ]);
        $before = $camera->only(array_keys($data));
        $camera->update($data);
        AuditLogger::log('camera.update', $camera, $before, $data);
        return back()->with('status', 'Camera updated.');
    }

    public function toggle(Request $request, Camera $camera)
    {
        $this->ensureOwned($request, $camera);
        $camera->update(['online' => ! $camera->online, 'last_seen_at' => now()]);
        AuditLogger::log('camera.toggle', $camera, [], ['online' => $camera->online]);
        return back()->with('status', 'Camera toggled.');
    }

    public function updateConfig(Request $request, Camera $camera)
    {
        $this->ensureOwned($request, $camera);
        $data = $request->validate([
            'thresholds' => ['nullable', 'array'],
            'retention_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'settings' => ['nullable', 'array'],
        ]);
        $config = CameraConfig::firstOrCreate(['camera_id' => $camera->id]);
        $config->update($data);
        AuditLogger::log('camera.config.update', $config, [], $data);
        return back()->with('status', 'Camera config saved.');
    }
}
