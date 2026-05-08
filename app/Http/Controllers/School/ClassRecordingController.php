<?php

namespace App\Http\Controllers\School;

use App\Models\ClassRecording;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class Recording controller — School-mode only.
 * Implements CLASS_RECORDING_CHECKLIST.md §5–§8.
 *
 * Authorization is server-side per §7:
 *   View    : MOE-tier (any), School Admin (own school), Teacher (own recordings)
 *   Download: MOE-tier (any), School Admin (own school) — Teacher CANNOT
 *   Preserve: MOE-tier (any), School Admin (own school) — Teacher CANNOT
 *   Destroy : MOE-tier (any), School Admin (own school) — Teacher CANNOT
 *
 * Feature is opt-in per school via Setting `class_recording_enabled`. When
 * disabled, every action 403s — direct URL access doesn't bypass the toggle.
 *
 * Streaming uses BinaryFileResponse which handles HTTP Range requests for
 * the HTML5 <video> element's seek behavior. Files MUST live on a private
 * disk; we never serve via the web server's static file handler because
 * that bypasses the auth check.
 */
class ClassRecordingController extends SchoolContextController
{
    /** Audit-log "viewed" rate limit (once per user-recording-hour). */
    private const VIEW_LOG_TTL_SECONDS = 3600;

    // -------- Index / detail / upload form --------

    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $this->assertRecordingsEnabled($school->id);

        $user = $request->user();
        $q = ClassRecording::query()
            ->where('school_id', $school->id)
            ->with(['teacher:id,name', 'schoolClass:id,name', 'preservedBy:id,name']);

        // Teacher tier sees only their own recordings (per §7.1).
        if ($this->tier($user) === 'teacher') {
            $q->where('teacher_user_id', $user->id);
        }

        // Filters per §10.1.
        if ($teacherId = $request->integer('teacher')) {
            $q->where('teacher_user_id', $teacherId);
        }
        if ($classId = $request->integer('class')) {
            $q->where('school_class_id', $classId);
        }
        if ($status = $request->string('status')->value()) {
            if (in_array($status, ClassRecording::STATUSES, true)) {
                $q->where('status', $status);
            }
        }
        if ($preserved = $request->string('preserved')->value()) {
            $q->where('preserved', $preserved === 'yes');
        }
        if ($from = $request->string('from')->value()) {
            $q->whereDate('started_at', '>=', $from);
        }
        if ($to = $request->string('to')->value()) {
            $q->whereDate('started_at', '<=', $to);
        }

        $recordings = $q->orderByDesc('started_at')->paginate(25)->withQueryString();

        $teachers = User::where('role', User::ROLE_TEACHER)
            ->where('school_id', $school->id)
            ->orderBy('name')->get(['id', 'name']);
        $classes = SchoolClass::where('school_id', $school->id)->orderBy('name')->get(['id', 'name']);
        $tier = $this->tier($user);

        return view('school.class-recordings.index', compact('recordings', 'teachers', 'classes', 'tier', 'school'));
    }

    public function create(Request $request)
    {
        $school = $this->requireSchool($request);
        $this->assertRecordingsEnabled($school->id);
        $this->authorizeUpload($request->user(), $school->id);

        $classes = SchoolClass::where('school_id', $school->id)->orderBy('name')->get(['id', 'name']);
        $teachers = User::where('role', User::ROLE_TEACHER)
            ->where('school_id', $school->id)
            ->orderBy('name')->get(['id', 'name']);
        $settings = ClassRecording::settingsForSchool($school->id);

        return view('school.class-recordings.create', compact('classes', 'teachers', 'settings'));
    }

    public function show(Request $request, ClassRecording $recording)
    {
        $school = $this->requireSchool($request);
        $this->assertRecordingsEnabled($school->id);
        $this->ensureOwned($request, $recording);
        $this->authorizeView($request->user(), $recording);

        $recording->load(['teacher:id,name,email', 'schoolClass:id,name', 'createdBy:id,name', 'preservedBy:id,name']);
        $tier = $this->tier($request->user());

        return view('school.class-recordings.show', compact('recording', 'tier', 'school'));
    }

    // -------- Upload --------

    public function store(Request $request)
    {
        $school = $this->requireSchool($request);
        $this->assertRecordingsEnabled($school->id);
        $this->authorizeUpload($request->user(), $school->id);

        $settings = ClassRecording::settingsForSchool($school->id);
        $maxKb = max(1, $settings[ClassRecording::SETTING_MAX_FILE_SIZE_MB] * 1024);
        $allowedMimes = $settings[ClassRecording::SETTING_ALLOWED_MIMES];

        $data = $request->validate([
            // mimetypes (NOT mimes) sniffs content, not extension — per §5.1.
            'file'            => ['required', 'file', 'mimetypes:' . implode(',', $allowedMimes), 'max:' . $maxKb],
            'school_class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'subject'         => ['nullable', 'string', 'max:255'],
            'teacher_user_id' => ['required', 'integer', 'exists:users,id'],
            'started_at'      => ['required', 'date'],
            'ended_at'        => ['nullable', 'date', 'after_or_equal:started_at'],
        ]);

        // The teacher attached must belong to this school AND have the teacher role.
        $teacher = User::where('id', $data['teacher_user_id'])
            ->where('school_id', $school->id)
            ->where('role', User::ROLE_TEACHER)
            ->first();
        if (! $teacher) {
            abort(422, 'Selected teacher is not on this school\'s roster.');
        }

        // School class must belong to this school (if provided).
        if (! empty($data['school_class_id'])) {
            $belongs = SchoolClass::where('id', $data['school_class_id'])
                ->where('school_id', $school->id)->exists();
            if (! $belongs) abort(422, 'Selected class is not on this school.');
        }

        $file = $request->file('file');
        $fileUuid = (string) Str::uuid();
        $extension = strtolower($file->getClientOriginalExtension() ?: 'mp4');
        $startedAt = now()->parse($data['started_at']);
        $path = ClassRecording::buildStoragePath($school->id, $fileUuid, $startedAt, $extension);

        // Store on the `local` disk (storage/app/private — outside the web root).
        // Files served only through the auth-gated stream/download routes.
        Storage::disk('local')->putFileAs(dirname($path), $file, basename($path));

        $duration = null;
        if (! empty($data['ended_at'])) {
            $duration = max(0, now()->parse($data['ended_at'])->diffInSeconds($startedAt));
        }

        $retentionDays = (int) $settings[ClassRecording::SETTING_RETENTION_DAYS];

        $recording = ClassRecording::create([
            'file_uuid'             => $fileUuid,
            'school_id'             => $school->id,
            'school_class_id'       => $data['school_class_id'] ?? null,
            'subject'               => $data['subject'] ?? null,
            'teacher_user_id'       => $teacher->id,
            'started_at'            => $startedAt,
            'ended_at'              => $data['ended_at'] ?? null,
            'duration_seconds'      => $duration,
            'file_disk'             => 'local',
            'file_path'             => $path,
            'file_size_bytes'       => $file->getSize(),
            'mime_type'             => $file->getMimeType() ?: 'video/mp4',
            'status'                => ClassRecording::STATUS_READY,
            'retention_expires_at'  => $startedAt->copy()->addDays($retentionDays),
            'preserved'             => false,
            'created_via'           => ClassRecording::CREATED_VIA_MANUAL,
            'created_by_user_id'    => $request->user()->id,
        ]);

        AuditLogger::log('class_recording.created', $recording, [], [
            'school_id'  => $school->id,
            'teacher_id' => $teacher->id,
            'started_at' => $startedAt->toIso8601String(),
            'size_bytes' => $file->getSize(),
        ], $school->id);

        return redirect()
            ->route('school.class-recordings.show', $recording)
            ->with('status', 'Recording uploaded.');
    }

    // -------- Stream (auth-gated bytes) --------

    public function stream(Request $request, ClassRecording $recording): BinaryFileResponse
    {
        $school = $this->requireSchool($request);
        $this->assertRecordingsEnabled($school->id);
        $this->ensureOwned($request, $recording);
        $this->authorizeView($request->user(), $recording);

        if (! $recording->isPlayable()) {
            abort(404, 'Recording is not currently playable.');
        }

        $disk = Storage::disk($recording->file_disk);
        if (! $disk->exists($recording->file_path)) {
            abort(404, 'Recording file is missing on storage.');
        }
        $absolutePath = $disk->path($recording->file_path);

        $this->logViewIfNotRateLimited($request->user()->id, $recording);

        // BinaryFileResponse handles HTTP Range requests automatically — required
        // for HTML5 <video> seek to work. Inline (not attachment) so the browser
        // plays it instead of downloading.
        $response = response()->file($absolutePath, [
            'Content-Type'        => $recording->mime_type ?: 'video/mp4',
            'Accept-Ranges'       => 'bytes',
            'Cache-Control'       => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
        return $response;
    }

    // -------- Download (raw file) --------

    public function download(Request $request, ClassRecording $recording): BinaryFileResponse
    {
        $school = $this->requireSchool($request);
        $this->assertRecordingsEnabled($school->id);
        $this->ensureOwned($request, $recording);
        $this->authorizeDownload($request->user(), $recording);

        $disk = Storage::disk($recording->file_disk);
        if (! $disk->exists($recording->file_path)) {
            abort(404, 'Recording file is missing on storage.');
        }

        $filename = sprintf(
            'recording-%s-%s.mp4',
            $recording->started_at->format('Y-m-d-His'),
            substr($recording->file_uuid, 0, 8)
        );

        AuditLogger::log('class_recording.downloaded', $recording, [], [
            'file_size_bytes' => $recording->file_size_bytes,
        ], $recording->school_id);

        return response()->download($disk->path($recording->file_path), $filename, [
            'Content-Type' => $recording->mime_type ?: 'video/mp4',
        ]);
    }

    // -------- Preserve / un-preserve --------

    public function preserve(Request $request, ClassRecording $recording)
    {
        $school = $this->requireSchool($request);
        $this->assertRecordingsEnabled($school->id);
        $this->ensureOwned($request, $recording);
        $this->authorizePreserve($request->user(), $recording);

        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $before = ['preserved' => $recording->preserved];

        $recording->update([
            'preserved'             => true,
            'preserved_at'          => now(),
            'preserved_by_user_id'  => $request->user()->id,
            'preserved_reason'      => $data['reason'],
        ]);

        AuditLogger::log('class_recording.preserved', $recording, $before, [
            'preserved' => true,
            'reason'    => $data['reason'],
        ], $recording->school_id);

        return back()->with('status', 'Recording preserved — exempt from auto-delete.');
    }

    public function unpreserve(Request $request, ClassRecording $recording)
    {
        $school = $this->requireSchool($request);
        $this->assertRecordingsEnabled($school->id);
        $this->ensureOwned($request, $recording);
        $this->authorizePreserve($request->user(), $recording);

        $before = [
            'preserved'        => $recording->preserved,
            'preserved_reason' => $recording->preserved_reason,
        ];

        $recording->update([
            'preserved'             => false,
            'preserved_at'          => null,
            'preserved_by_user_id'  => null,
            'preserved_reason'      => null,
        ]);

        AuditLogger::log('class_recording.unpreserved', $recording, $before, [
            'preserved' => false,
        ], $recording->school_id);

        return back()->with('status', 'Recording un-preserved — subject to auto-delete again.');
    }

    // -------- Destroy --------

    public function destroy(Request $request, ClassRecording $recording)
    {
        $school = $this->requireSchool($request);
        $this->assertRecordingsEnabled($school->id);
        $this->ensureOwned($request, $recording);
        $this->authorizeDestroy($request->user(), $recording);

        $disk = Storage::disk($recording->file_disk);
        if ($disk->exists($recording->file_path)) {
            $disk->delete($recording->file_path);
        }

        $recording->update([
            'status' => ClassRecording::STATUS_ARCHIVED,
            'file_size_bytes' => 0,
        ]);
        $recording->delete(); // soft delete — keep metadata row for audit trail

        AuditLogger::log('class_recording.deleted', $recording, [], [
            'reason' => 'manual',
        ], $recording->school_id);

        return redirect()
            ->route('school.class-recordings.index')
            ->with('status', 'Recording deleted (file removed; metadata retained for audit).');
    }

    // ====================================================================
    // Authorization helpers — server-side per §7.4. UI hiding is for UX,
    // not security; these gates are the actual enforcement.
    // ====================================================================

    /** Returns the access tier for a user: 'moe' | 'school_admin' | 'teacher' | null. */
    private function tier(?User $user): ?string
    {
        if (! $user) return null;
        if ($user->isMoe() || $user->isMoeViewer()) return 'moe';
        if ($user->isSchoolAdmin())                 return 'school_admin';
        if ($user->role === User::ROLE_TEACHER)     return 'teacher';
        return null;
    }

    private function authorizeView(?User $user, ClassRecording $recording): void
    {
        $tier = $this->tier($user);
        if ($tier === 'moe' || $tier === 'school_admin') return;
        if ($tier === 'teacher' && (int) $recording->teacher_user_id === (int) $user->id) return;
        abort(403, 'You are not authorized to view this recording.');
    }

    private function authorizeDownload(?User $user, ClassRecording $recording): void
    {
        $tier = $this->tier($user);
        if ($tier === 'moe' || $tier === 'school_admin') return;
        // Teachers cannot download — even their own (per §7.2).
        abort(403, 'You are not authorized to download this recording.');
    }

    private function authorizePreserve(?User $user, ClassRecording $recording): void
    {
        $tier = $this->tier($user);
        if ($tier === 'moe' || $tier === 'school_admin') return;
        abort(403, 'You are not authorized to change preservation on this recording.');
    }

    private function authorizeDestroy(?User $user, ClassRecording $recording): void
    {
        $tier = $this->tier($user);
        if ($tier === 'moe' || $tier === 'school_admin') return;
        abort(403, 'You are not authorized to delete this recording.');
    }

    private function authorizeUpload(?User $user, int $schoolId): void
    {
        $tier = $this->tier($user);
        if ($tier === 'moe' || $tier === 'school_admin') return;
        // Teachers can upload their own recordings.
        if ($tier === 'teacher' && (int) $user->school_id === $schoolId) return;
        abort(403, 'You are not authorized to upload recordings.');
    }

    // ====================================================================
    // Smartboard upload — SCAFFOLDING per §5.2.
    //
    // Real smartboard hardware integration is OUT OF SCOPE for v1. This
    // endpoint exists so future integrators have a target shape to build
    // against — behavior may change before real hardware is wired. v1 fails
    // closed: when no IPG_SMARTBOARD_TOKEN is configured, the endpoint
    // 401s ALL callers regardless of input.
    //
    // Auth: shared bearer token via config('class_recording.smartboard.token').
    // For production, swap for Sanctum personal access tokens, mTLS, or
    // device certs — choose based on hardware capabilities.
    // ====================================================================
    public function smartboardUpload(\Illuminate\Http\Request $request)
    {
        $expected = config('class_recording.smartboard.token');
        $provided = $request->bearerToken();
        if (empty($expected) || empty($provided) || ! hash_equals((string) $expected, (string) $provided)) {
            abort(401, 'Invalid or missing smartboard bearer token.');
        }

        $request->validate([
            'school_id'             => ['required', 'integer', 'exists:schools,id'],
            'school_class_id'       => ['nullable', 'integer', 'exists:school_classes,id'],
            'subject'               => ['nullable', 'string', 'max:255'],
            'teacher_user_id'       => ['required', 'integer', 'exists:users,id'],
            'started_at'            => ['required', 'date'],
            'ended_at'              => ['nullable', 'date', 'after_or_equal:started_at'],
            'smartboard_device_id'  => ['required', 'string', 'max:128'],
            'file'                  => ['required', 'file', 'mimetypes:video/mp4'],
        ]);

        // v1 stub: validates the request shape so integrators can develop
        // against a stable contract, but does NOT actually persist anything.
        // Wire real persistence (mirroring store()) once hardware specifics
        // are settled — at that point this stub returns 201 with the row id.
        return response()->json([
            'status'  => 'not_implemented',
            'message' => 'Smartboard upload endpoint is scaffolded for v2. Use the manual upload UI under /school/class-recordings/create for v1.',
        ], 501);
    }

    private function assertRecordingsEnabled(int $schoolId): void
    {
        if (! ClassRecording::isEnabledForSchool($schoolId)) {
            abort(403, 'Class Recording is not enabled for this school.');
        }
    }

    /**
     * Logs a `class_recording.viewed` audit entry, but rate-limited to one
     * write per user-recording per VIEW_LOG_TTL_SECONDS (default 1h) to
     * avoid log spam from repeated playback / scrubbing.
     */
    private function logViewIfNotRateLimited(int $userId, ClassRecording $recording): void
    {
        $cacheKey = "audit:class_recording.viewed:u={$userId}:r={$recording->id}";
        if (Cache::has($cacheKey)) return;
        Cache::put($cacheKey, 1, self::VIEW_LOG_TTL_SECONDS);
        AuditLogger::log('class_recording.viewed', $recording, [], [], $recording->school_id);
    }
}
