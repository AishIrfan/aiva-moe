<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ModeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\School as S;
use App\Http\Controllers\Moe as M;
use App\Http\Controllers\IPG\IPGController;
use App\Http\Controllers\BPG\BPGController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::post('/mode', [ModeController::class, 'switch'])->name('mode.switch');
    Route::get('/search', SearchController::class)->name('search');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dashboard default
    Route::get('/dashboard', fn () => redirect()->route('school.overview'))->name('dashboard');

    // ============ SCHOOL MODE ============
    Route::prefix('school')->name('school.')->group(function () {
        Route::get('/overview', [S\OverviewController::class, 'index'])->name('overview');
        Route::get('/live', [S\LiveController::class, 'index'])->name('live');

        // Alerts & Incidents
        Route::get('/alerts', [S\AlertsController::class, 'index'])->name('alerts');
        Route::post('/alerts/{event}/acknowledge', [S\AlertsController::class, 'acknowledge'])->name('alerts.acknowledge');
        Route::post('/alerts/{event}/escalate', [S\AlertsController::class, 'escalate'])->name('alerts.escalate');
        Route::post('/alerts/{event}/close', [S\AlertsController::class, 'close'])->name('alerts.close');
        Route::post('/alerts/{event}/assign', [S\AlertsController::class, 'assign'])->name('alerts.assign');

        // Attendance (+ sub views)
        Route::get('/attendance', [S\AttendanceController::class, 'index'])->name('attendance');
        Route::post('/attendance/override', [S\AttendanceController::class, 'override'])->name('attendance.override');
        Route::get('/attendance-follow-up', [S\AttendanceController::class, 'followUp'])->name('attendance-follow-up');
        Route::get('/attendance-records', [S\AttendanceController::class, 'records'])->name('attendance-records');
        Route::get('/attendance-monthly-summary', [S\AttendanceController::class, 'monthly'])->name('attendance-monthly-summary');
        Route::get('/attendance-warning-letters', [S\AttendanceController::class, 'warningLetters'])->name('attendance-warning-letters');

        Route::get('/analytics', [S\AnalyticsController::class, 'index'])->name('analytics');

        // Safety
        Route::get('/safety', [S\SafetyController::class, 'index'])->name('safety');
        Route::post('/safety/incident', [S\SafetyController::class, 'createIncident'])->name('safety.incident');
        Route::post('/safety/broadcast', [S\SafetyController::class, 'broadcast'])->name('safety.broadcast');

        // Cameras
        Route::get('/cameras', [S\CameraController::class, 'index'])->name('cameras');
        Route::post('/cameras', [S\CameraController::class, 'store'])->name('cameras.store');
        Route::put('/cameras/{camera}', [S\CameraController::class, 'update'])->name('cameras.update');
        Route::post('/cameras/{camera}/toggle', [S\CameraController::class, 'toggle'])->name('cameras.toggle');
        Route::post('/cameras/{camera}/config', [S\CameraController::class, 'updateConfig'])->name('cameras.config');

        Route::get('/relationship', [S\RelationshipController::class, 'index'])->name('relationship');
        Route::get('/reports', [S\ReportsController::class, 'index'])->name('reports');
        Route::post('/reports', [S\ReportsController::class, 'store'])->name('reports.store');

        // Academics
        Route::get('/grades-classes', [S\GradesClassesController::class, 'index'])->name('grades-classes');
        Route::post('/grades', [S\GradesClassesController::class, 'storeGrade'])->name('grades.store');
        Route::put('/grades/{grade}', [S\GradesClassesController::class, 'updateGrade'])->name('grades.update');
        Route::post('/classes', [S\GradesClassesController::class, 'storeClass'])->name('classes.store');
        Route::put('/classes/{class}', [S\GradesClassesController::class, 'updateClass'])->name('classes.update');

        Route::get('/enrollment', [S\EnrollmentController::class, 'index'])->name('enrollment');
        Route::post('/enrollment/assign', [S\EnrollmentController::class, 'assign'])->name('enrollment.assign');
        Route::post('/enrollment/transfer', [S\EnrollmentController::class, 'transfer'])->name('enrollment.transfer');

        Route::get('/timetables', [S\TimetablesController::class, 'index'])->name('timetables');

        // Students
        Route::get('/students', [S\StudentsController::class, 'index'])->name('students');
        Route::get('/student-360', [S\StudentsController::class, 'student360'])->name('student-360');
        Route::post('/students/{student}/notes', [S\StudentsController::class, 'addNote'])->name('students.notes.store');
        Route::post('/students/{student}/incident', [S\StudentsController::class, 'addIncident'])->name('students.incident');

        // Leaves (simple)
        Route::get('/leaves', [S\LeavesController::class, 'index'])->name('leaves');
        Route::post('/leaves', [S\LeavesController::class, 'store'])->name('leaves.store');
        Route::post('/leaves/{leaveRequest}/decide', [S\LeavesController::class, 'decide'])->name('leaves.decide');

        // Assistance
        Route::get('/assistance', [S\AssistanceController::class, 'index'])->name('assistance');
        Route::post('/assistance/programs', [S\AssistanceController::class, 'storeProgram'])->name('assistance.programs.store');
        Route::put('/assistance/programs/{program}', [S\AssistanceController::class, 'updateProgram'])->name('assistance.programs.update');
        Route::post('/assistance/applications', [S\AssistanceController::class, 'submit'])->name('assistance.apply');
        Route::post('/assistance/applications/{application}/verify', [S\AssistanceController::class, 'verify'])->name('assistance.verify');
        Route::post('/assistance/applications/{application}/decide', [S\AssistanceController::class, 'decide'])->name('assistance.decide');
        Route::post('/assistance/applications/{application}/disburse', [S\AssistanceController::class, 'disburse'])->name('assistance.disburse');
        Route::get('/assistance/export', [S\AssistanceController::class, 'export'])->name('assistance.export');

        // Documents
        Route::get('/documents', [S\DocumentsController::class, 'index'])->name('documents');
        Route::post('/documents', [S\DocumentsController::class, 'store'])->name('documents.store');
        Route::put('/documents/{document}', [S\DocumentsController::class, 'update'])->name('documents.update');
        Route::post('/documents/{document}/link', [S\DocumentsController::class, 'link'])->name('documents.link');
        Route::delete('/documents/{document}/link', [S\DocumentsController::class, 'unlink'])->name('documents.unlink');
        Route::post('/documents/{document}/ack', [S\DocumentsController::class, 'ack'])->name('documents.ack');

        Route::get('/face-enrollment', [S\FaceEnrollmentController::class, 'index'])->name('face-enrollment');
        Route::post('/face-enrollment/person', [S\FaceEnrollmentController::class, 'createPerson'])->name('face-enrollment.person');
        Route::delete('/face-enrollment/person/{personId}', [S\FaceEnrollmentController::class, 'removePerson'])->name('face-enrollment.person.remove');

        // Chat
        Route::get('/chat', [S\ChatController::class, 'index'])->name('chat');
        Route::post('/chat/{conversation}/messages', [S\ChatController::class, 'sendMessage'])->name('chat.message');
        Route::post('/chat/{conversation}/status', [S\ChatController::class, 'setStatus'])->name('chat.status');
        Route::post('/chat/messages/{message}/flag', [S\ChatController::class, 'flag'])->name('chat.flag');
        Route::post('/chat/broadcast', [S\ChatController::class, 'broadcast'])->name('chat.broadcast');

        // Management Workflows (Phase 5)
        Route::get('/schedule', [S\ScheduleController::class, 'index'])->name('schedule');
        Route::post('/schedule', [S\ScheduleController::class, 'store'])->name('schedule.store');
        Route::put('/schedule/{schedule}', [S\ScheduleController::class, 'update'])->name('schedule.update');
        Route::delete('/schedule/{schedule}', [S\ScheduleController::class, 'destroy'])->name('schedule.destroy');
        Route::post('/schedule/replacement', [S\ScheduleController::class, 'storeReplacement'])->name('schedule.replacement');
        Route::get('/schedule/export', [S\ScheduleController::class, 'export'])->name('schedule.export');

        Route::get('/events-management', [S\EventsManagementController::class, 'index'])->name('events-management');
        Route::post('/events-management', [S\EventsManagementController::class, 'store'])->name('events-management.store');
        Route::put('/events-management/{event}', [S\EventsManagementController::class, 'update'])->name('events-management.update');
        Route::post('/events-management/{event}/transition', [S\EventsManagementController::class, 'transition'])->name('events-management.transition');
        Route::post('/events-management/{event}/participants', [S\EventsManagementController::class, 'addParticipant'])->name('events-management.participants');
        Route::post('/events-management/{event}/attendance', [S\EventsManagementController::class, 'markAttendance'])->name('events-management.attendance');
        Route::post('/events-management/{event}/approve', [S\EventsManagementController::class, 'approve'])->name('events-management.approve');
        Route::get('/events-management/export', [S\EventsManagementController::class, 'export'])->name('events-management.export');

        Route::get('/surat-cuti-mc', [S\LeaveManagementController::class, 'index'])->name('surat-cuti-mc');
        Route::post('/surat-cuti-mc', [S\LeaveManagementController::class, 'store'])->name('surat-cuti-mc.store');
        Route::post('/surat-cuti-mc/{submission}/transition', [S\LeaveManagementController::class, 'transition'])->name('surat-cuti-mc.transition');
        Route::post('/surat-cuti-mc/{submission}/attachments', [S\LeaveManagementController::class, 'addAttachment'])->name('surat-cuti-mc.attachment');

        Route::get('/laporan-masalah-disiplin', [S\DisciplineController::class, 'index'])->name('laporan-masalah-disiplin');
        Route::post('/laporan-masalah-disiplin', [S\DisciplineController::class, 'store'])->name('laporan-masalah-disiplin.store');
        Route::post('/laporan-masalah-disiplin/{case}/transition', [S\DisciplineController::class, 'transition'])->name('laporan-masalah-disiplin.transition');
        Route::post('/laporan-masalah-disiplin/{case}/actions', [S\DisciplineController::class, 'addAction'])->name('laporan-masalah-disiplin.action');
        Route::post('/laporan-masalah-disiplin/{case}/evidence', [S\DisciplineController::class, 'addEvidence'])->name('laporan-masalah-disiplin.evidence');

        Route::get('/settings', [S\SettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [S\SettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/sensestudio/test', [S\SettingsController::class, 'testSenseStudio'])->name('settings.sensestudio.test');
    });

    // ============ MOE MODE ============
    // moe_viewer is the school-side ministry parallel to BPG on the IPG side —
    // full ministry-level oversight of the School modules but explicitly NO
    // IPG / BPG access (those route groups don't whitelist moe_viewer).
    Route::prefix('moe')->name('moe.')->middleware('role:moe_admin,moe_viewer')->group(function () {
        Route::get('/overview', [M\OverviewController::class, 'index'])->name('overview');
        Route::get('/schools', [M\SchoolsController::class, 'index'])->name('schools');
        Route::post('/schools/select', [M\SchoolsController::class, 'select'])->name('schools.select');
        Route::get('/trends', [M\TrendsController::class, 'index'])->name('trends');
    });

    // ============ IPG MODE (scaffold) ============
    // Surface aligned with IPG_MODE_CHECKLIST.md §7. Removed Live/Cameras/Safety/
    // Relationships/Alerts/Analytics/Face Enrollment. Renamed Schedule→Academic
    // Calendar, Students→Trainees, Grades & Classes→Grades & Cohorts. Added
    // Transcripts/Co-curriculum/Research, the Practicum section, and Hostel.
    // Action endpoints (POST/PUT/DELETE) hit IPGController@stub until each module
    // is promoted from scaffold.
    Route::prefix('ipg')->name('ipg.')->middleware('role:ipg_admin,bpg_admin,moe_admin,ketua_jabatan,pensyarah,trainee')->group(function () {
        // ----- Campus System -----
        Route::get('/overview',                            [IPGController::class, 'overview'])->name('overview');
        Route::get('/academic-calendar',                   [IPGController::class, 'academicCalendar'])->name('academic-calendar');

        // ----- Academics -----
        Route::get('/grades-cohorts',                      [IPGController::class, 'gradesCohorts'])->name('grades-cohorts');
        Route::post('/cohorts',                            [IPGController::class, 'stub'])->name('cohorts.store');
        Route::put('/cohorts/{cohort}',                    [IPGController::class, 'stub'])->name('cohorts.update');

        Route::get('/enrollment',                          [IPGController::class, 'enrollment'])->name('enrollment');
        Route::post('/enrollment/assign',                  [IPGController::class, 'stub'])->name('enrollment.assign');
        Route::post('/enrollment/transfer',                [IPGController::class, 'stub'])->name('enrollment.transfer');

        Route::get('/timetables',                          [IPGController::class, 'timetables'])->name('timetables');

        Route::get('/transcripts',                         [IPGController::class, 'transcripts'])->name('transcripts');
        Route::get('/cocurriculum',                        [IPGController::class, 'cocurriculum'])->name('cocurriculum');
        Route::get('/research',                            [IPGController::class, 'research'])->name('research');

        // ----- Practicum (interconnect surface to School mode) -----
        Route::prefix('practicum')->name('practicum.')->group(function () {
            Route::get('/placements',                      [IPGController::class, 'practicumPlacements'])->name('placements');
            Route::get('/supervisors',                     [IPGController::class, 'practicumSupervisors'])->name('supervisors');
            Route::get('/observations',                    [IPGController::class, 'practicumObservations'])->name('observations');
            Route::get('/evaluations',                     [IPGController::class, 'practicumEvaluations'])->name('evaluations');
            Route::get('/logbook',                         [IPGController::class, 'practicumLogbook'])->name('logbook');
            Route::get('/coordination',                    [IPGController::class, 'practicumCoordination'])->name('coordination');
        });

        // ----- Trainees -----
        Route::get('/trainees',                            [IPGController::class, 'trainees'])->name('trainees');
        Route::get('/trainee-360',                         [IPGController::class, 'trainee360'])->name('trainee-360');
        Route::post('/trainees/{trainee}/notes',           [IPGController::class, 'stub'])->name('trainees.notes.store');
        Route::post('/trainees/{trainee}/incident',        [IPGController::class, 'stub'])->name('trainees.incident');

        Route::get('/leaves',                              [IPGController::class, 'leaves'])->name('leaves');
        Route::post('/leaves',                             [IPGController::class, 'stub'])->name('leaves.store');
        Route::post('/leaves/{leaveRequest}/decide',       [IPGController::class, 'stub'])->name('leaves.decide');

        Route::get('/assistance',                          [IPGController::class, 'assistance'])->name('assistance');
        Route::post('/assistance/programs',                [IPGController::class, 'stub'])->name('assistance.programs.store');
        Route::put('/assistance/programs/{program}',       [IPGController::class, 'stub'])->name('assistance.programs.update');
        Route::post('/assistance/applications',            [IPGController::class, 'stub'])->name('assistance.apply');
        Route::post('/assistance/applications/{application}/verify',   [IPGController::class, 'stub'])->name('assistance.verify');
        Route::post('/assistance/applications/{application}/decide',   [IPGController::class, 'stub'])->name('assistance.decide');
        Route::post('/assistance/applications/{application}/disburse', [IPGController::class, 'stub'])->name('assistance.disburse');
        Route::get('/assistance/export',                   [IPGController::class, 'stub'])->name('assistance.export');

        Route::get('/hostel',                              [IPGController::class, 'hostel'])->name('hostel');

        // ----- Communication -----
        Route::get('/chat',                                [IPGController::class, 'chat'])->name('chat');
        Route::post('/chat/{conversation}/messages',       [IPGController::class, 'stub'])->name('chat.message');
        Route::post('/chat/{conversation}/status',         [IPGController::class, 'stub'])->name('chat.status');
        Route::post('/chat/messages/{message}/flag',       [IPGController::class, 'stub'])->name('chat.flag');
        Route::post('/chat/broadcast',                     [IPGController::class, 'stub'])->name('chat.broadcast');

        Route::get('/documents',                           [IPGController::class, 'documents'])->name('documents');
        Route::post('/documents',                          [IPGController::class, 'stub'])->name('documents.store');
        Route::put('/documents/{document}',                [IPGController::class, 'stub'])->name('documents.update');
        Route::post('/documents/{document}/link',          [IPGController::class, 'stub'])->name('documents.link');
        Route::delete('/documents/{document}/link',        [IPGController::class, 'stub'])->name('documents.unlink');
        Route::post('/documents/{document}/ack',           [IPGController::class, 'stub'])->name('documents.ack');

        // ----- Management -----
        Route::get('/events-management',                   [IPGController::class, 'eventsManagement'])->name('events-management');
        Route::post('/events-management',                  [IPGController::class, 'stub'])->name('events-management.store');
        Route::put('/events-management/{event}',           [IPGController::class, 'stub'])->name('events-management.update');
        Route::post('/events-management/{event}/transition',   [IPGController::class, 'stub'])->name('events-management.transition');
        Route::post('/events-management/{event}/participants', [IPGController::class, 'stub'])->name('events-management.participants');
        Route::post('/events-management/{event}/attendance',   [IPGController::class, 'stub'])->name('events-management.attendance');
        Route::post('/events-management/{event}/approve',      [IPGController::class, 'stub'])->name('events-management.approve');
        Route::get('/events-management/export',            [IPGController::class, 'stub'])->name('events-management.export');

        Route::get('/surat-cuti-mc',                       [IPGController::class, 'suratCutiMc'])->name('surat-cuti-mc');
        Route::post('/surat-cuti-mc',                      [IPGController::class, 'stub'])->name('surat-cuti-mc.store');
        Route::post('/surat-cuti-mc/{submission}/transition',  [IPGController::class, 'stub'])->name('surat-cuti-mc.transition');
        Route::post('/surat-cuti-mc/{submission}/attachments', [IPGController::class, 'stub'])->name('surat-cuti-mc.attachment');

        Route::get('/laporan-masalah-disiplin',            [IPGController::class, 'laporanDisiplin'])->name('laporan-masalah-disiplin');
        Route::post('/laporan-masalah-disiplin',           [IPGController::class, 'stub'])->name('laporan-masalah-disiplin.store');
        Route::post('/laporan-masalah-disiplin/{case}/transition', [IPGController::class, 'stub'])->name('laporan-masalah-disiplin.transition');
        Route::post('/laporan-masalah-disiplin/{case}/actions',    [IPGController::class, 'stub'])->name('laporan-masalah-disiplin.action');
        Route::post('/laporan-masalah-disiplin/{case}/evidence',   [IPGController::class, 'stub'])->name('laporan-masalah-disiplin.evidence');

        Route::get('/attendance',                          [IPGController::class, 'attendance'])->name('attendance');
        Route::post('/attendance/override',                [IPGController::class, 'stub'])->name('attendance.override');
        Route::get('/attendance-follow-up',                [IPGController::class, 'attendanceFollowUp'])->name('attendance-follow-up');
        Route::get('/attendance-records',                  [IPGController::class, 'attendanceRecords'])->name('attendance-records');
        Route::get('/attendance-monthly-summary',          [IPGController::class, 'attendanceMonthlySummary'])->name('attendance-monthly-summary');
        Route::get('/attendance-warning-letters',          [IPGController::class, 'attendanceWarningLetters'])->name('attendance-warning-letters');

        Route::get('/reports',                             [IPGController::class, 'reports'])->name('reports');
        Route::post('/reports',                            [IPGController::class, 'stub'])->name('reports.store');

        Route::get('/settings',                            [IPGController::class, 'settings'])->name('settings');
        Route::post('/settings',                           [IPGController::class, 'stub'])->name('settings.update');
    });

    // ============ BPG (Bahagian Pendidikan Guru) — IPG ministry layer ============
    // BPG users live inside IPG mode but see ministry-level pages here. Picking a
    // campus drops them into the regular IPG sidebar scoped to that campus
    // (mirrors the MOE → "select a school" flow).
    Route::prefix('bpg')->name('bpg.')->middleware('role:bpg_admin,moe_admin')->group(function () {
        Route::get('/overview',          [BPGController::class, 'overview'])->name('overview');
        Route::get('/campuses',          [BPGController::class, 'campuses'])->name('campuses');
        Route::post('/campuses/select',  [BPGController::class, 'selectCampus'])->name('campuses.select');
        Route::get('/trends',            [BPGController::class, 'trends'])->name('trends');
    });
});

require __DIR__.'/auth.php';
