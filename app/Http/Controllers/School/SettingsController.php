<?php

namespace App\Http\Controllers\School;

use App\Models\ClassRecording;
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
            'class_recording' => ClassRecording::settingsForSchool($school->id),
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
            // Class recording — bounded validation per CLASS_RECORDING_CHECKLIST §3.
            'class_recording_enabled'          => ['nullable', 'boolean'],
            'class_recording_audio_enabled'    => ['nullable', 'boolean'],
            'class_recording_retention_days'   => ['nullable', 'integer', 'min:'.ClassRecording::RETENTION_MIN_DAYS, 'max:'.ClassRecording::RETENTION_MAX_DAYS],
            'class_recording_max_file_size_mb' => ['nullable', 'integer', 'min:1', 'max:8192'],
        ]);

        foreach (['notifications','thresholds','retention','sensestudio'] as $key) {
            if (isset($data[$key]) && $data[$key] !== null) {
                Setting::putSchoolValue($school->id, $key, $data[$key]);
            }
        }

        // Class recording settings — write only the keys actually present so
        // posting only one toggle doesn't reset the others to defaults.
        $crKeys = [
            ClassRecording::SETTING_ENABLED          => 'class_recording_enabled',
            ClassRecording::SETTING_AUDIO_ENABLED    => 'class_recording_audio_enabled',
            ClassRecording::SETTING_RETENTION_DAYS   => 'class_recording_retention_days',
            ClassRecording::SETTING_MAX_FILE_SIZE_MB => 'class_recording_max_file_size_mb',
        ];
        $touchedClassRecording = [];
        foreach ($crKeys as $settingKey => $inputKey) {
            if ($request->has($inputKey)) {
                $value = $request->input($inputKey);
                // Checkboxes: present-but-empty input means "off" (1 vs absent).
                if (in_array($inputKey, ['class_recording_enabled','class_recording_audio_enabled'], true)) {
                    $value = (bool) $value;
                } else {
                    $value = (int) $value;
                }
                Setting::putSchoolValue($school->id, $settingKey, $value);
                $touchedClassRecording[$settingKey] = $value;
            }
        }
        // Checkbox inputs that aren't submitted at all = unchecked → false. Only
        // apply this for the two booleans, and only if the form posted ANY
        // class_recording_* field (so we know this submission is the CR form).
        if ($request->has('class_recording_form_submitted')) {
            foreach ([ClassRecording::SETTING_ENABLED => 'class_recording_enabled',
                      ClassRecording::SETTING_AUDIO_ENABLED => 'class_recording_audio_enabled'] as $sk => $ik) {
                if (! $request->has($ik)) {
                    Setting::putSchoolValue($school->id, $sk, false);
                    $touchedClassRecording[$sk] = false;
                }
            }
        }

        $touched = array_keys(array_filter($data, fn($v) => $v !== null));
        if ($touchedClassRecording) {
            $touched[] = 'class_recording';
        }
        AuditLogger::log('settings.update', null, [], ['keys' => $touched, 'class_recording' => $touchedClassRecording ?: null]);
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
