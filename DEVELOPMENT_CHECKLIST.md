# AIVA MOE — Laravel Development Checklist

Based on `system-modules/` documentation. The `.env` is already configured for `moe-laravel.weststar-dev.com` with MySQL, database sessions, database cache, database queue, and LFTP deployment settings.

Legend: `[ ]` = to do · `[~]` = partial (exists in prototype, needs hardening) · `[x]` = done

---

## Phase 0 — Project Bootstrap

- [ ] Run `composer create-project laravel/laravel .` inside `moe-laravel.weststar-dev.com/` (preserve existing `.env`)
- [ ] Verify `.env` loads: `APP_KEY`, `APP_URL`, `DB_*`, `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`
- [ ] Uncomment and confirm `DB_HOST`, `DB_PORT`, `DB_DATABASE=weststar_moe-laravel`, `DB_USERNAME`, `DB_PASSWORD`
- [ ] `php artisan session:table && php artisan cache:table && php artisan queue:table && php artisan migrate`
- [ ] Install frontend toolchain: `npm install` (Vite) and confirm `VITE_APP_NAME`
- [ ] Configure LFTP deploy script using `LFTP_HOST`, `LFTP_USERNAME`, `LFTP_PASSWORD`, `LFTP_PATH`
- [ ] Set up `.well-known/` passthrough (folder exists) and `cgi-bin/` routing on shared host
- [ ] Add `.gitignore` / init git if needed

---

## Phase 1 — Shared Platform & Auth

Reference: `shared-platform-and-auth.md`

### 1.1 Auth (replace prototype `aiva_authed` localStorage flag)
- [ ] Install Laravel Breeze or Fortify (session-based, no Jetstream)
- [ ] Users table migration (extend default) — replaces stub `api/users.php`
- [ ] Roles: at minimum `moe_admin`, `school_admin`, `teacher`, `operator`
- [ ] Login page (migrate `login.html` + `js/pages/login.js`)
- [ ] Registration page (migrate `register.html`)
- [ ] Forgot-password flow (request → sent → reset) wired to mail
- [ ] Password hashing via bcrypt (rounds=12 per `.env`)
- [ ] Middleware: `auth`, `role:<name>`, `mode:school|moe`
- [ ] Logout clears session and any SPA state cookies

### 1.2 App shell / SPA hosting
- [ ] Decide: keep hash-SPA (`#/school/...`) or migrate to Inertia/Livewire
- [ ] Blade layout replacing `index.html` (sidebar, topbar, content, footer slots)
- [ ] Port `js/app.js` route registry to a mode-aware router
- [ ] Port `js/router.js` hash navigation (or replace with Inertia page map)
- [ ] Port `js/state.js` → split: server-authoritative (DB) vs UI-only (localStorage)
    - mode, selectedSchoolId, sidebar visibility → localStorage
    - user, role, settings → server session
- [ ] Port `js/components/sidebar.js` with school/MOE mode groups
- [ ] Port `js/components/topbar.js` (title, search, health, notifications, profile)
- [ ] Port `js/components/toast.js` and `menu-modal.js`
- [ ] Remove/retire legacy pages: `attendance.html`, `classroom.html`, `partials/*.html`

---

## Phase 2 — Database Schema (runtime domains → migrations)

Reference: README.md runtime data collections.

### 2.1 Core academic entities
- [ ] `schools` migration + model + factory + seeder
- [ ] `grades` migration + model + factory
- [ ] `classes` migration + model (FK grade, school, teacher)
- [ ] `teachers` migration + model (or role on users)
- [ ] `subjects` migration + model
- [ ] `terms` migration + model (academic year + term)
- [ ] `periods` migration + model (time slots)
- [ ] `schedules` migration + model (FK class, teacher, subject, period, day)
- [ ] `students` migration + model
- [ ] `enrollments` migration + model (student↔class, active flag, reason, history)
- [ ] `guardians` migration + model (pivot to students)

### 2.2 Operational / safety entities
- [ ] `events` (incidents/alerts) — supports AIVA, FR, BAT subtypes
- [ ] `broadcasts`
- [ ] `reports`
- [ ] `audit_logs` (generic, polymorphic)
- [ ] `cameras`
- [ ] `camera_configs`
- [ ] `zones`
- [ ] `attendance_snapshots` (by date, by school, by student)

### 2.3 Student services / communication
- [ ] `leave_requests` (simple school leaves)
- [ ] `leave_submissions` (structured Surat Cuti/MC with attachments, status flow)
- [ ] `leave_attachments`
- [ ] `discipline_cases` + `discipline_actions` + `discipline_evidence`
- [ ] `student_notes`
- [ ] `assistance_programs`
- [ ] `assistance_applications`
- [ ] `documents`
- [ ] `document_links` (polymorphic: class, student)
- [ ] `document_acknowledgments`
- [ ] `conversations` / `messages` / `chat_broadcasts`
- [ ] `events_management` tables (events, participants, letters, attendance, approvals)

### 2.4 Integrations
- [ ] `sensestudio_settings` (or JSON column on settings)
- [ ] `fr_events` + `fr_event_attributes` (port from `api/fr_event_get.php`)
- [ ] `bat_events` + `bat_event_attributes` + `bat_event_trigger_targets`
- [ ] `fr_event_trigger_targets`

---

## Phase 3 — School Core Modules

Reference: `school-core-modules.md`

### 3.1 School Overview `#/school/overview`
- [ ] Dashboard controller + Blade/Vue view
- [ ] Aggregators: alerts summary, camera uptime, attendance metrics, hot zones, recent activity
- [ ] Uses: events, cameras, attendance_snapshots, selected school

### 3.2 Live Monitor `#/school/live`
- [ ] Live view page with active event/camera context
- [ ] WebSocket or polling channel (consider Laravel Reverb/Pusher since `BROADCAST_CONNECTION=log` currently)

### 3.3 Alerts & Incidents `#/school/alerts` [~]
- [ ] Alert list with filters
- [ ] Acknowledge, escalate, close, assign actions
- [ ] Evidence verify + notes
- [ ] Parent notification trigger (hooks into Chat)
- [ ] Audit log entries on each action

### 3.4 Attendance `#/school/attendance` [~]
- [ ] Daily attendance seeding job (replaces `seedAttendance()`)
- [ ] Attendance model scoped by date + school
- [ ] Manual override endpoint with audit trail
- [ ] Overlay approved leaves onto attendance (`applyLeaveToAttendance`)
- [ ] Follow-up view `#/school/attendance-follow-up`
- [ ] Records view `#/school/attendance-records`
- [ ] Monthly summary `#/school/attendance-monthly-summary`
- [ ] Warning letters `#/school/attendance-warning-letters`

### 3.5 Analytics `#/school/analytics`
- [ ] Analytics controller with event pattern aggregation
- [ ] Behavior insights queries
- [ ] Chart components (consider Chart.js/ApexCharts)

### 3.6 Safety & Emergency `#/school/safety` [~]
- [ ] Manual event creation endpoint (`createEvent`)
- [ ] Broadcast send endpoint (`sendBroadcast`)
- [ ] SOP action buttons

### 3.7 Cameras & Zones `#/school/cameras` [~]
- [ ] Camera CRUD + online/offline toggle
- [ ] Zone mapping CRUD
- [ ] Threshold + retention per-camera overrides
- [ ] `ensureCameraConfig`, `updateCamera`, `updateCameraConfig`, `updateZone` endpoints

### 3.8 Relationship Mapping `#/school/relationship`
- [ ] Graph data endpoint (events ↔ students ↔ incidents)
- [ ] Graph visualization (D3/vis.js)

### 3.9 Reports `#/school/reports` [~]
- [ ] Report generation (`addReport`)
- [ ] Report history list
- [ ] PDF export (consider `barryvdh/laravel-dompdf`)

---

## Phase 4 — School Academics & Students

Reference: `school-academics-and-students.md`

### 4.1 Grades & Classes `#/school/grades-classes`
- [ ] CRUD controllers for grades and classes
- [ ] Teacher assignment
- [ ] Audit log wiring

### 4.2 Enrollment `#/school/enrollment` [~]
- [ ] Assign student to class endpoint
- [ ] Transfer with required reason
- [ ] Active vs prior enrollment history view
- [ ] Port `seedEnrollments`, `getActiveEnrollment`, `assignStudentToClass`

### 4.3 Timetables `#/school/timetables` [~]
- [ ] View by class and by teacher
- [ ] Create/update/delete schedule entries
- [ ] Clash detection (teacher, class, room)
- [ ] Port `upsertSchedule`, `deleteSchedule`

### 4.4 Students `#/school/students`
- [ ] Searchable paginated student list
- [ ] Link to Student 360
- [ ] Attendance context inline

### 4.5 Student 360 `#/school/student-360` [~]  **HIGH PRIORITY**
- [ ] Profile + class context
- [ ] Attendance summary widget
- [ ] Leave history
- [ ] Incident history
- [ ] Guardian info
- [ ] Notes (add via `addStudentNote`)
- [ ] Assistance applications (submit via `submitAssistanceApplication`)
- [ ] Linked documents + acknowledgments (`setDocumentAck`)
- [ ] Manual incident creation (`createEvent`)

### 4.6 Leaves `#/school/leaves` [~]
- [ ] Submit / approve / reject flow (`createLeaveRequest`, `decideLeave`)
- [ ] Apply decision to attendance (`applyLeaveToAttendance`)
- [ ] Toast feedback on save

### 4.7 Assistance `#/school/assistance` [~]
- [ ] Programs CRUD
- [ ] Applications submit → verify → approve/reject → disburse
- [ ] CSV export
- [ ] Port all assistance lifecycle functions

### 4.8 Documents `#/school/documents` [~]
- [ ] Document CRUD with file upload (local disk per `FILESYSTEM_DISK=local`)
- [ ] Link/unlink to classes or students (polymorphic)
- [ ] Acknowledgment tracking
- [ ] `createDocument`, `updateDocument`, `linkDocument`, `unlinkDocument`, `setDocumentAck`

### 4.9 Face Enrollment `#/school/face-enrollment` [~]
- [ ] Port `js/lib/sensestudio.js` to a Laravel service class
- [ ] Library create endpoint
- [ ] Person create for student
- [ ] Face image upload and enroll
- [ ] Remove person
- [ ] Error surface to UI via toasts

### 4.10 Chat `#/school/chat` [~]
- [ ] Conversation inbox
- [ ] Send message endpoint
- [ ] Status updates, flag message
- [ ] Broadcast tab (`sendChatBroadcast`)
- [ ] Realtime via broadcast channel (upgrade `BROADCAST_CONNECTION` from `log`)

---

## Phase 5 — School Management Workflows

Reference: `school-management-workflows.md` — these are the most mature prototype modules; port their service/state/validation structure to Laravel services + FormRequests.

### 5.1 Schedule `#/school/schedule`
- [ ] Port `schedule_service.js` → `App\Services\ScheduleService`
- [ ] Port `schedule_utils.js` validators → FormRequest rules
- [ ] Master / class / teacher / room / replacement tabs
- [ ] Daily and weekly views
- [ ] Filters: year, term, class, teacher, room, day, search
- [ ] Schedule create/edit modal
- [ ] Replacement/relief class modal
- [ ] Conflict detection (teacher/class/room)
- [ ] `deriveTimeSlots`, `overlayScheduleForDate` logic
- [ ] Print + CSV export
- [ ] Migrate from localStorage → DB-backed

### 5.2 Events Management `#/school/events-management`
- [ ] Port `event_service.js` → `App\Services\EventManagementService`
- [ ] Port `event_validation.js` → FormRequests
- [ ] Tabs: list, details, participants, letters, attendance, approvals, report
- [ ] Status machine: draft → pending_approval → approved → ongoing → completed / cancelled / rejected / returned_for_revision
- [ ] Participant deduplication
- [ ] Overlap checks
- [ ] Letter generation + preview (PDF)
- [ ] Print + CSV export

### 5.3 Surat Cuti / MC `#/school/surat-cuti-mc`
- [ ] Port `leave_service.js` → `App\Services\LeaveSubmissionService`
- [ ] Tabs: new, list, pending, history, student-history, attachments
- [ ] Status machine: draft → submitted → pending_review → approved / rejected / cancelled / returned_for_revision
- [ ] Duplicate range detection
- [ ] Leave-day calculation
- [ ] File type/size validation
- [ ] Attendance impact tracking (integrates with Phase 3.4)
- [ ] Review modal + attachment preview
- [ ] Print + CSV export

### 5.4 Laporan Masalah Disiplin `#/school/laporan-masalah-disiplin`
- [ ] Port `discipline_service.js` → `App\Services\DisciplineService`
- [ ] Tabs: new, list, pending, details, actions, parent, counseling, history, reports, evidence
- [ ] Status machine: draft → submitted → pending_review → under_investigation → action_required → resolved / closed / rejected / cancelled
- [ ] Duplicate case detection
- [ ] Evidence upload + preview
- [ ] Parent notification + counseling referral workflows
- [ ] Repeat-offense summary
- [ ] Case timeline view
- [ ] Print + CSV export

### 5.5 Settings `#/school/settings` [~]
- [ ] User identity edit
- [ ] Notification routing config
- [ ] Thresholds config (used by analytics + alerts)
- [ ] Retention config (used by cameras)
- [ ] SenseStudio connection fields (connect/disconnect test)
- [ ] Persist to DB per-user and per-school

---

## Phase 6 — MOE Mode

Reference: `moe-modules.md`

### 6.1 MOE Overview `#/moe/overview`
- [ ] Cross-school risk, uptime, attendance, incident aggregators
- [ ] Role guard: `moe_admin` only

### 6.2 MOE Schools `#/moe/schools`
- [ ] School listing with filter/sort
- [ ] Select school → write to shared state + redirect to `#/school/overview`

### 6.3 MOE Trends `#/moe/trends`
- [ ] Aggregate incident rate queries
- [ ] Distribution comparisons
- [ ] Time-series charts

---

## Phase 7 — Backend API Parity

Reference: `backend-and-integrations.md`

### 7.1 API client (`js/lib/api.js`)
- [ ] Rewrite as Axios/Fetch client pointing to Laravel routes
- [ ] JSON request/response, query params, error envelope: `{ ok, error, data }`
- [ ] Wrappers: Students, Schools, Classes, Grades, Users, AbsentReasons, Schedules, AIVAEvents, FREvents, BATEvents

### 7.2 Replace PHP endpoints with Laravel controllers
Working/partial PHP → Laravel:
- [ ] `api/students.php` → `StudentController` (API resource)
- [ ] `api/schools.php` → `SchoolController`
- [ ] `api/grades.php` → `GradeController`
- [ ] `api/users.php` → `UserController` (auth already via Breeze)
- [ ] `api/absent_reasons.php` → `AbsentReasonController` + default seeder

Empty/incomplete PHP → implement fully in Laravel:
- [ ] `api/classes.php` → `ClassController`
- [ ] `api/schedules.php` → `ScheduleController`
- [ ] `api/aiva_events.php` → `AivaEventController`
- [ ] `api/bat_event_trigger_targets.php` → `BatEventTriggerTargetController`

### 7.3 Event ingestion endpoints
- [ ] Port `api/fr_event_get.php` → `FrEventController@index` with attribute + person enrichment
- [ ] Port `api/bat_event_get.php` → `BatEventController@index` with attribute + trigger + policy + crowd metrics
- [ ] Port `api/fr_event_trigger_targets.php` → `FrEventTriggerController@store`
    - [ ] Validate incoming payload
    - [ ] Store FR event + attributes
    - [ ] Update Firebase latest-event key (config via `.env`)
    - [ ] Call remote image-flush endpoint (queue job)

### 7.4 SenseStudio integration
- [ ] `App\Services\SenseStudio\Client`
- [ ] Connection settings storage (DB)
- [ ] Auth token handling (refresh, expiry)
- [ ] Device access methods
- [ ] Person library CRUD
- [ ] Face enrollment
- [ ] Event queries
- [ ] Connectivity health check

### 7.5 Mock / simulation
- [ ] Port `js/mock.js` as an artisan command (`php artisan mock:realtime`) behind `APP_ENV !== production` guard
- [ ] Optional WebSocket channel for demo broadcasting

---

## Phase 8 — Cross-cutting Concerns

- [ ] Request validation (FormRequest for every write endpoint)
- [ ] Policies + Gates per model (school scoping, role scoping)
- [ ] Global `school_id` scope middleware for school-mode routes
- [ ] Audit log observer (auto-write on model events)
- [ ] File storage: local disk now; S3-ready (`AWS_*` vars already in `.env`)
- [ ] Queue workers for: FR image flush, letter PDF generation, mail, broadcasts
- [ ] Scheduler (`app/Console/Kernel.php`): daily attendance seed, retention purge, analytics rollup
- [ ] Mail from `MAIL_*` config (currently `log` driver — upgrade before prod)
- [ ] Localization: `APP_LOCALE=en`, `APP_FALLBACK_LOCALE=en` — add `ms` (Malay) translations for Surat Cuti, Laporan Disiplin
- [ ] Logging: keep `stack`/`single` for now, ensure sensitive data is scrubbed
- [ ] CSRF protection on all non-API forms
- [ ] Rate limiting on auth + FR ingestion endpoints

---

## Phase 9 — Testing

- [ ] Pest or PHPUnit setup
- [ ] Feature tests per controller (happy path + auth)
- [ ] Unit tests for services (Schedule, Event, Leave, Discipline — port their validation rules)
- [ ] Integration test for FR event ingestion (mock upstream)
- [ ] Browser tests (Dusk) for: login, student 360, alert acknowledge, schedule clash, leave submission

---

## Phase 10 — Deployment

- [ ] Build pipeline: `npm run build` + `composer install --optimize-autoloader --no-dev`
- [ ] `php artisan config:cache && route:cache && view:cache && event:cache`
- [ ] LFTP deploy script using `LFTP_*` creds in `.env`
- [ ] `.well-known/` remains accessible post-deploy
- [ ] DB migrate on deploy (consider `--force` in prod)
- [ ] Storage symlink: `php artisan storage:link`
- [ ] Health check route
- [ ] Error monitoring (Sentry/Flare optional)
- [ ] Flip `APP_DEBUG=false`, `APP_ENV=production` before prod cutover
- [ ] Upgrade `MAIL_MAILER` away from `log` for prod
- [ ] Upgrade `BROADCAST_CONNECTION` away from `log` if realtime is needed

---

## Priority order (suggested)

1. Phase 0 bootstrap
2. Phase 1 Auth + shell (unblocks every page)
3. Phase 2 core schema (schools, grades, classes, students, enrollments)
4. Phase 7.2 working CRUD endpoints (Students, Schools, Grades, Classes)
5. Phase 4 Students + Student 360 (highest operational value per docs)
6. Phase 3.3 Alerts + 3.4 Attendance (main day-to-day use)
7. Phase 5 management workflows (largest porting effort — do after core is stable)
8. Phase 7.3 + 7.4 integrations (FR/BAT/SenseStudio) — can run in parallel with Phase 5
9. Phase 6 MOE mode (depends on multi-school data existing)
10. Phase 8–10 hardening, tests, deploy

---

## Maturity notes from docs (inform porting effort)

**More mature in prototype (port existing logic carefully):**
- Alerts, Attendance, Cameras, Safety, Reports
- Schedule, Events Management, Surat Cuti / MC, Laporan Masalah Disiplin
- Student 360, Face Enrollment
- FR/BAT event ingestion

**Thinner in prototype (design from scratch in Laravel):**
- Overview, Live, Analytics, Relationship Mapping
- MOE Overview, MOE Schools, MOE Trends
- Empty PHP endpoints (classes, schedules, aiva_events, bat_event_trigger_targets)
- Real auth (currently localStorage flag only)
