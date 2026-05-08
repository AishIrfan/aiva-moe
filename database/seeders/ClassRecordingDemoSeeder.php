<?php

namespace Database\Seeders;

use App\Models\ClassRecording;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seeds demo class recordings per CLASS_RECORDING_CHECKLIST.md §11.
 *
 * Idempotent — uses updateOrCreate keyed on (school_id, started_at, subject).
 * Anchors started_at to a fixed reference date (2026-04-30) so re-runs
 * produce stable rows; recordings will look historic over time but the
 * seed never accumulates duplicates.
 *
 * Files written to disk are PLACEHOLDER bytes (short MP4-ish ftyp prefix
 * + ~1KB padding) labelled video/mp4 but not actually playable. Per §11 +
 * §14: never seed real classroom footage. The list view, metadata panel,
 * and audit-trail panel all render correctly with these placeholders;
 * playback shows the browser's "video failed to load" state, which is the
 * intended demo experience for a non-uploaded recording.
 *
 * Required scenarios covered (§11):
 *   - At least one preserved recording (exempt from auto-delete) ✓
 *   - At least one near-retention-expiry recording ✓
 *   - At least one already-archived recording (file gone, metadata kept) ✓
 *   - Spread across multiple dates within / past the retention window ✓
 */
class ClassRecordingDemoSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::orderBy('id')->first();
        if (! $school) return;

        // Enable the feature for this school so the sidebar entry surfaces
        // and the routes don't 403. Audio stays OFF (privacy default).
        Setting::putSchoolValue($school->id, ClassRecording::SETTING_ENABLED, true);
        Setting::putSchoolValue($school->id, ClassRecording::SETTING_AUDIO_ENABLED, false);
        Setting::putSchoolValue($school->id, ClassRecording::SETTING_RETENTION_DAYS, ClassRecording::DEFAULT_RETENTION_DAYS);

        $teachers = User::where('role', User::ROLE_TEACHER)
            ->where('school_id', $school->id)
            ->orderBy('id')->get();
        $classes = SchoolClass::where('school_id', $school->id)->orderBy('id')->get();
        if ($teachers->isEmpty() || $classes->isEmpty()) return;

        $admin = User::where('role', User::ROLE_SCHOOL_ADMIN)
            ->where('school_id', $school->id)->first();

        $ref = Carbon::parse('2026-04-30 09:00:00');
        $retentionDays = ClassRecording::DEFAULT_RETENTION_DAYS;

        // Placeholder MP4 bytes: a minimal ftyp box header + padding. Browsers
        // recognize the MIME signature but cannot decode — ideal for a demo
        // file that surfaces the playback-error UI without shipping real
        // classroom footage.
        $placeholder = "\x00\x00\x00\x18ftyp" . "isom\x00\x00\x00\x00" . "isommp42" . str_repeat("\x00", 1000);

        // [days_offset_from_ref, teacher_idx, class_idx, subject, status, preserved, write_file]
        $specs = [
            [-2,  0, 0, 'Mathematics — Trigonometry',           'ready',    false, true],
            [-3,  0, 1, 'Bahasa Melayu — Karangan Naratif',     'ready',    false, false],
            [-5,  0, 2, 'Science — Photosynthesis',             'ready',    true,  true],   // PRESERVED
            [-10, 0, 3, 'English — Reading Comprehension',      'ready',    false, false],
            [-29, 0, 0, 'Mathematics — Algebraic Identities',   'ready',    false, false],  // NEAR EXPIRY (1d remaining)
            [-60, 0, 1, 'Bahasa Melayu — Komsas',               'archived', false, false],  // ALREADY ARCHIVED (file gone)
        ];

        foreach ($specs as $i => [$daysOffset, $tIdx, $cIdx, $subject, $status, $preserved, $writeFile]) {
            $teacher = $teachers[$tIdx % $teachers->count()];
            $class   = $classes[$cIdx % $classes->count()];
            $startedAt = $ref->copy()->addDays($daysOffset)->addHours($i);
            $endedAt   = $startedAt->copy()->addMinutes(45);

            // Reuse file_uuid for existing rows so re-seeds keep paths stable.
            $existing = ClassRecording::where('school_id', $school->id)
                ->where('started_at', $startedAt)
                ->where('subject', $subject)
                ->first();
            $fileUuid = $existing?->file_uuid ?? (string) Str::uuid();
            $path = ClassRecording::buildStoragePath($school->id, $fileUuid, $startedAt, 'mp4');

            ClassRecording::updateOrCreate(
                [
                    'school_id'  => $school->id,
                    'started_at' => $startedAt,
                    'subject'    => $subject,
                ],
                [
                    'file_uuid'             => $fileUuid,
                    'school_class_id'       => $class->id,
                    'teacher_user_id'       => $teacher->id,
                    'ended_at'              => $endedAt,
                    'duration_seconds'      => 45 * 60,
                    'file_disk'             => 'local',
                    'file_path'             => $path,
                    'file_size_bytes'       => $writeFile ? strlen($placeholder) : 0,
                    'mime_type'             => 'video/mp4',
                    'status'                => $status,
                    'retention_expires_at'  => $startedAt->copy()->addDays($retentionDays),
                    'preserved'             => $preserved,
                    'preserved_at'          => $preserved ? $startedAt->copy()->addDay() : null,
                    'preserved_by_user_id'  => $preserved ? ($admin?->id ?? $teacher->id) : null,
                    'preserved_reason'      => $preserved ? 'Demo: pinned for safety review training case study.' : null,
                    'created_via'           => ClassRecording::CREATED_VIA_MANUAL,
                    'created_by_user_id'    => $teacher->id,
                ]
            );

            if ($writeFile && ! Storage::disk('local')->exists($path)) {
                Storage::disk('local')->put($path, $placeholder);
            }
        }
    }
}
