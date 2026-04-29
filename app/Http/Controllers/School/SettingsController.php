<?php

namespace App\Http\Controllers\School;

use App\Models\Setting;
use App\Services\AuditLogger;
use App\Services\SenseStudio\Client;
use Illuminate\Http\Request;

class SettingsController extends SchoolContextController
{
    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $settings = [
            'notifications' => Setting::schoolValue($school->id, 'notifications', ['email' => true, 'sms' => false]),
            'thresholds' => Setting::schoolValue($school->id, 'thresholds', ['late_minutes' => 15, 'crowd_warn' => 50]),
            'retention' => Setting::schoolValue($school->id, 'retention', ['days' => 30]),
            'sensestudio' => Setting::schoolValue($school->id, 'sensestudio', ['base_url' => '', 'username' => '']),
        ];
        return view('school.settings', compact('school', 'settings'));
    }

    public function update(Request $request)
    {
        $school = $this->requireSchool($request);
        $data = $request->validate([
            'notifications' => ['nullable', 'array'],
            'thresholds' => ['nullable', 'array'],
            'retention' => ['nullable', 'array'],
            'sensestudio' => ['nullable', 'array'],
        ]);
        foreach ($data as $key => $value) {
            if ($value !== null) Setting::putSchoolValue($school->id, $key, $value);
        }
        AuditLogger::log('settings.update', null, [], ['keys' => array_keys(array_filter($data))]);
        return back()->with('status', 'Settings saved.');
    }

    public function testSenseStudio(Request $request)
    {
        $school = $this->requireSchool($request);
        $data = $request->validate([
            'base_url' => ['required', 'url'],
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
        try {
            Setting::putSchoolValue($school->id, 'sensestudio', ['base_url' => $data['base_url']]);
            $client = new Client($school->id);
            $token = $client->authenticate($data['username'], $data['password']);
            // Persist URL + username + token. NEVER store the plaintext password.
            Setting::putSchoolValue($school->id, 'sensestudio', [
                'base_url' => $data['base_url'],
                'username' => $data['username'],
                'token' => $token,
                'connected_at' => now()->toIso8601String(),
            ]);
            return back()->with('status', 'Connected to SenseStudio.');
        } catch (\Throwable $e) {
            return back()->withErrors(['sensestudio' => $e->getMessage()]);
        }
    }
}
