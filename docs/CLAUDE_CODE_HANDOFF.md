# Claude Code — Wave 1 Handoff

> **Date:** 2026-04-27
> **Last session ended at:** 2% context remaining, immediately after Wave 1 completion verified.
> **Author:** previous Claude session (Sonnet/Opus, with 1M context).
> **Read first:** `IPG_MODE_CHECKLIST.md` and `IPG_WORKFLOWS.md` at project root. They are the authoritative spec; this handoff is implementation state.

---

## TL;DR

**Wave 1 (foundation data models) is COMPLETE and verified.** Migrations ran clean, seed populated, build clean (67KB CSS / 12KB gzipped). The user explicitly said "stop at Wave 1 boundary" — no in-progress work, no half-written files, no uncommitted intent.

The next session should:
1. Read both planning docs + this file.
2. Run `php artisan migrate:status` to confirm schema state.
3. Wait for the user's go on Wave 2 (Hat 1 mini-LMS — coursework/attendance/leave/discipline) before writing anything.

---

## 1. Wave 1 status — what landed

All seven Wave 1 migrations executed successfully. Schema is on disk, models are on disk, seeder populates them, `IPG mode` browser-verifiable end-to-end.

### Migrations (chronological, all applied)

| Date stamp | File | What it adds |
|---|---|---|
| 2026_04_27_120000 | `create_practicum_windows.php` | `practicum_windows` + `practicum_window_cohorts` join |
| 2026_04_27_120100 | `link_placements_to_windows.php` | `placements.practicum_window_id` (nullable FK) |
| 2026_04_27_121000 | `create_course_offerings.php` | `course_offerings` (Course × Cohort × Semester × Lecturer) |
| 2026_04_27_121100 | `create_timetable_sessions.php` | `timetable_sessions` (recurring weekly slots) |
| 2026_04_27_122000 | `create_observation_rubrics.php` | `observation_rubrics` + `observation_rubric_categories` + `campuses.current_observation_rubric_id` |
| 2026_04_27_123000 | `create_placement_letter_templates.php` | `placement_letter_templates` + `campuses.current_placement_letter_template_id` |
| 2026_04_27_124000 | `create_approved_practicum_schools.php` | `approved_practicum_schools` (hard FK to schools.id, cascade on delete) |

### Models created (7)

| Model | File | Notes |
|---|---|---|
| `PracticumWindow` | `app/Models/PracticumWindow.php` | Status constants `STATUS_DRAFT/ACTIVE/CLOSED/CANCELLED`; `cohorts()` belongsToMany; `eligibleTrainees()` derivation. |
| `CourseOffering` | `app/Models/CourseOffering.php` | `STATUS_ACTIVE/ARCHIVED`; `display_label` accessor. |
| `TimetableSession` | `app/Models/TimetableSession.php` | `DAY_MONDAY..DAY_SUNDAY` constants (ISO 1–7). |
| `ObservationRubric` | `app/Models/ObservationRubric.php` | `STATUS_DRAFT/ACTIVE/RETIRED`; `max_total_score` accessor. |
| `ObservationRubricCategory` | `app/Models/ObservationRubricCategory.php` | Per-category `max_score` (author-defined scale, no global enum). |
| `PlacementLetterTemplate` | `app/Models/PlacementLetterTemplate.php` | `render(array $values)` method does `str_replace` on `{placeholder}` tokens — deliberately NOT Blade, stored content stays non-executable. |
| `ApprovedPracticumSchool` | `app/Models/ApprovedPracticumSchool.php` | Cross-mode bridge with hard FK to `schools.id`. |

### Models modified (5)

| File | Change |
|---|---|
| `app/Models/Placement.php` | Added 7 status constants (`STATUS_PLACED / PENDING_ACKNOWLEDGEMENT / CONFIRMED / ACTIVE / COMPLETED / WITHDRAWN / CANCELLED`); `VISIBLE_TO_HOST_SCHOOL = [confirmed, active]` array; `window()` belongsTo. |
| `app/Models/Cohort.php` | Added `practicumWindows()` belongsToMany + `BelongsToMany` import. |
| `app/Models/Campus.php` | Added `practicumWindows()`, `approvedPracticumSchools()`, `currentObservationRubric()`, `currentPlacementLetterTemplate()`; added `BelongsTo` import. |
| `app/Models/Course.php` | Added `offerings()` hasMany. |
| `app/Models/Pensyarah.php` | Added `MAX_TRAINEE_LOAD = 8` class constant with TODO pointer to future `ipg_settings`; added `courseOfferings()` hasMany. |

### Services modified (2)

| File | Change |
|---|---|
| `app/Services/PracticumProjection.php` | Filter changed from `activeOn($date) AND status IN (active, scheduled)` to `status IN VISIBLE_TO_HOST_SCHOOL AND end_date >= $date`. The `activeOn()` start-date guard was over-restrictive — `confirmed` placements now correctly surface to host schools before start_date so they can prepare. |
| `app/Services/AuditLogger.php` | Added `IPG_PREFIX = 'ipg.'`, `BPG_PREFIX = 'bpg.'` constants. Added `logIpg($entityVerb, ...)` convenience method that auto-prefixes. Class docblock documents the convention: `ipg.<entity>.<verb>` for IPG-mode events (e.g. `ipg.placement.confirmed`), `bpg.<entity>.<verb>` for ministry events, school-mode actions remain unprefixed. |

### Seeder modified (1)

`database/seeders/IpgDemoSeeder.php` — six new methods added, all idempotent:
- `seedObservationRubric($campus)` — 6-category BPG rubric, pinned on campus
- `seedPlacementLetterTemplate($campus)` — Bahasa Malaysia template with 8 named placeholders, pinned on campus
- `seedApprovedPracticumSchools($campus)` — approves all schools for the campus
- `seedPracticumWindow($campus)` — 1 active window covering all cohorts; returns the window
- `seedCourseOfferings($campus, $semester)` — 15 offerings (5 courses × 3 cohorts), rotated lecturers
- `seedTimetableSessions($campus, $semester)` — 15 weekly slots, rotated days/periods/rooms
- `seedPracticum()` signature extended with optional `?PracticumWindow $window` parameter; placement statuses now use new state machine values

### Verified DB state (post-seed)

```
PracticumWindow:           1   ("Praktikum PISMP — Fasa 2 (Demo)", 3 cohorts attached)
CourseOffering:           15
TimetableSession:         15
ObservationRubric:         1   (6 categories, 1–4 scale)
PlacementLetterTemplate:   1
ApprovedPracticumSchool:   1
Placements:                4   (1 active, 1 confirmed, 1 completed, 1 pending_acknowledgement)

Campus 1 pinned:
  current_observation_rubric_id        → "BPG Praktikum Observation Rubric v2025.1"
  current_placement_letter_template_id → "BPG Standard Placement Letter v2025.1"

PracticumProjection (school 1):
  Aiman Hakimi   [active]    start=Apr 6
  Nurul Aisyah   [confirmed] start=May 4   ← previously hidden by activeOn() bug, now correctly surfaces
```

---

## 2. Decisions locked in (Wave 1 approval message + earlier turns)

These are now binding; do not relitigate without explicit user re-approval.

1. **Window === Phase (v1)** — phase encoded as `practicum_windows.name` text only. No phase enum.
2. **Approved schools registry** — separate `approved_practicum_schools` join table, not a flag on `schools`. Hard FK to `schools.id` with cascade on delete.
3. **BPG templates** — global versioned rows; each Campus pins via `current_observation_rubric_id` / `current_placement_letter_template_id` FKs. **In Wave 3, store the rubric_id ON each `observations` row** so finalised observations remain immutable when BPG publishes a new rubric version.
4. **Trainee-tag projection** — tightened to `status IN (confirmed, active) AND end_date >= today`. Treated as a correctness fix, already shipped.
5. **Single primary lecturer** on `course_offerings` (`lecturer_pensyarah_id` column). Co-teaching via a future `course_offering_lecturers` pivot if/when needed — deferred.
6. **Unified `assessments` table with `kind` enum** for Wave 2 mini-LMS: `manual_grade / assignment / tutorial / f2f_test / online_test`. Kind-specific data goes in **adjunct tables** (e.g. `online_test_questions`), NOT JSON. JSON is fine only for sparse optional config.
7. **Notifications: deferred.** No `notifications` table in v1. Workflows degrade to in-app badges where needed.
8. **Observation/Evaluation seed (Wave 3)** — wipe and regenerate when expanding the schema. **Wipe must be explicit** in the seeder reset path, not silent.
9. **Audit log** — reuse School mode's polymorphic `audit_logs` table. **Convention: `ipg.<entity>.<verb>`** action prefix for IPG events, `bpg.<entity>.<verb>` for BPG events. School actions unprefixed (back-compat).
10. **Pensyarah workload max** — hardcoded `Pensyarah::MAX_TRAINEE_LOAD = 8` constant with TODO. Do NOT create an `ipg_settings` table for a single value. Promote when a second IPG-wide config value emerges.
11. **Cross-mode FKs** — `approved_practicum_schools.school_id` is a hard DB-level FK to `schools.id`, not a loose bigint reference.
12. **Naming consistency** — `created_by_user_id` / `updated_by_user_id` on all Wave 1 tables. Use these names everywhere going forward.
13. **Wave 1 explicit deferrals** — NO trainee self-evaluation table, NO online-test-attempts/responses tables. Both will be designed alongside trainee workflows in a later wave.

---

## 3. Ambiguities resolved this session

The one-paragraph clarifications I issued before Wave 1 coding started:

- **Rubric category scoring scale** → per-category `max_score` integer set by rubric author (no global enum). Default seeded rubric uses `max_score = 4` per category (1–4 scale).
- **Window status enum** → `draft / active / closed / cancelled` (cancelled per W3.1's edge case).
- **Placement letter body format** → plain text with named `{placeholder}` tokens, rendered via `str_replace` in `PlacementLetterTemplate::render()`. Deliberately not Blade.
- **Audit-log convention** → established as `AuditLogger::IPG_PREFIX` / `BPG_PREFIX` constants with namespaced action strings.

---

## 4. Open ambiguities NOT yet resolved (for Wave 2+ planning)

These were flagged in my pre-Wave-1 analysis but deliberately deferred. They will need user input when their wave is planned:

- **W1.7 mini-LMS group submissions** — Pensyarah-defined vs trainee-self-formed groups. Trainee self-formation flow is part of trainee workflows (deferred).
- **Course material file storage** — local disk vs S3-style; `disk` config; max file size default (workflow says 50MB). Not yet decided.
- **Coursework adjunct table shape** — user said "adjunct tables, not JSON" but the exact split per kind (e.g. `assignment_settings`, `f2f_test_settings`, `online_test_settings`) hasn't been specified.
- **Per-session attendance vs per-day** — IPG attendance is per-session (W1.1), distinct from school-mode daily `attendance_snapshots`. New table `ipg_attendance_sessions` + `ipg_attendance_records` needed in Wave 2.
- **IpgLeaveRequest vs school-mode LeaveRequest** — separate tables. `ipg_leave_requests` + `ipg_leave_request_pensyarah_responses` (per W1.4 — Pensyarah's response is course-impact, NOT approval; IPG Admin approves separately).
- **IpgDisciplineCase shape** — distinct from school-mode `discipline_cases`. Categories per W1.5: Akademik / Kelakuan / Kehadiran / Etika / Lain-lain. Severity enum: Minor/Moderate/Serious. Status flow: Submitted → Under Review → Action Taken/Dismissed.
- **Research milestones split** (Wave 4) — currently `research_projects.milestones` is a JSON column. W1.8 wants milestones as proper rows with status, target dates, attached documents, and threaded comments. Migration in Wave 4.
- **Trainee self-evaluation** (referenced by W2.5 but explicitly deferred per locked decision #13) — schema will be designed when trainee workflows are mapped.
- **Online test attempts/responses** (W1.7.5, deferred per #13) — same as above.

---

## 5. In-progress / incomplete work

**None.** The session ended cleanly at the Wave 1 boundary. Specifically:

- All 7 Wave 1 migrations applied successfully (verified via `php artisan migrate:status`).
- All seed data populated (verified via tinker counts — see §1 "Verified DB state").
- Build is clean (`npm run build` ran successfully, 67.20 KB CSS / 11.98 KB gzipped, 1.46s).
- No half-written migrations, models, or methods.
- No leftover scaffolding referencing not-yet-created tables.
- No git state — project is not a git repo per environment metadata, so no uncommitted changes to flag. The user said "commit per coherent unit" — I interpreted as logical chunks for the user's own version control workflow.

The only "drift" worth flagging: `PracticumProjection` was tightened during verification when I noticed the `activeOn()` filter was hiding `confirmed` placements (Nurul Aisyah, start_date in future). This was inside Unit 6 scope but discovered during verification. Already shipped in this session.

---

## 6. Files modified this session — comprehensive list

### Pre-Wave-1 (role-based visibility work, earlier in the session)

| File | Status | Change |
|---|---|---|
| `app/Models/User.php` | modified | Added `ROLE_KETUA_JABATAN`, `ROLE_PENSYARAH`, `ROLE_TRAINEE` constants; `IPG_ROLES` array; `isKetuaJabatan() / isPensyarah() / isTrainee()` helpers; `livesInIpgMode()` predicate; rewrote `defaultMode()` |
| `app/Http/Controllers/ModeController.php` | modified | Switched from `isIpg() \|\| isBpg()` checks to `livesInIpgMode()` |
| `routes/web.php` | modified | IPG route group middleware widened to `role:ipg_admin,bpg_admin,moe_admin,ketua_jabatan,pensyarah,trainee` |
| `resources/views/partials/sidebar.blade.php` | modified | Major refactor: 5 role-based sidebar variants (`$ipgGroupsAdmin / KetuaJabatan / Penyelaras / Pensyarah / Trainee`); role badge below campus chip; sidebar scroll persistence via Alpine + localStorage |
| `app/Http/Controllers/IPG/IPGController.php` | modified | `trainee360()` and `transcripts()` lock to `$user->trainee` for trainee role (privacy) |
| `app/Models/Pensyarah.php` | modified | Added `is_ketua_jabatan` cast |
| `database/migrations/2026_04_27_090000_add_role_flags_to_pensyarahs.php` | created | `is_ketua_jabatan` + `major_scope` columns on `pensyarahs` |
| `database/seeders/DatabaseSeeder.php` | modified | Added 4 new IPG role test accounts (kj.bm / penyelaras / pensyarah / trainee) wired to existing pensyarah/trainee rows; promoted Dr. Faiz Ramlan to KJ Bahasa Melayu |

### Wave 1 (this session's main work)

**Migrations created (7):** see §1 "Migrations" table above.

**Models created (7):**
- `app/Models/PracticumWindow.php`
- `app/Models/CourseOffering.php`
- `app/Models/TimetableSession.php`
- `app/Models/ObservationRubric.php`
- `app/Models/ObservationRubricCategory.php`
- `app/Models/PlacementLetterTemplate.php`
- `app/Models/ApprovedPracticumSchool.php`

**Models modified:** see §1 "Models modified" table above.

**Services modified:** `PracticumProjection.php`, `AuditLogger.php` — see §1.

**Seeder modified:** `database/seeders/IpgDemoSeeder.php` — see §1.

### Documentation written this session
- `docs/CLAUDE_CODE_HANDOFF.md` — this file.

---

## 7. Test accounts (all password: `password`)

| Email | Role | Notes |
|---|---|---|
| `admin@aiva.test` | MOE superadmin | Full mode-switcher, sees all three modes |
| `bpg@aiva.test` | BPG | IPG mode + Ministry (BPG) section, picks campus |
| `ipg@aiva.test` | IPG Admin | Full IPG sidebar, campus pre-selected |
| `kj.bm@aiva.test` | Ketua Jabatan (Bahasa Melayu) | Full IPG nav minus Settings + amber KJ badge |
| `penyelaras@aiva.test` | Penyelaras Praktikum | Pensyarah view + campus-wide Practicum |
| `pensyarah@aiva.test` | Pensyarah (regular) | Trimmed nav + practicum scoped to assigned trainees only (`Placement::scopeVisibleTo`) |
| `trainee@aiva.test` | Guru Pelatih | "MY ..." sidebar, locked to own trainee record |
| `school@aiva.test` | School Admin | School mode (SMK Demo) — sees IPG trainee tag projection |
| `teacher@aiva.test` | Teacher | School mode |

---

## 8. The exact next 3 actions

For a fresh session resuming this work:

1. **Read `IPG_MODE_CHECKLIST.md`, `IPG_WORKFLOWS.md`, and this handoff fully** before doing anything else. Then run `php artisan migrate:status` and confirm all migrations through `2026_04_27_124000_create_approved_practicum_schools` show `Ran`. If any are `Pending`, run `php artisan migrate`. Run `php artisan db:seed --force` if the seeded counts in §1 don't match.

2. **Wait for the user to give the green light on Wave 2** (Hat 1 mini-LMS — coursework, attendance, leave, discipline). Do NOT start coding Wave 2 unprompted. Match the planning ritual the user established for Wave 1: deliver a one-paragraph remaining-ambiguity summary first, propose unit boundaries, then await approval.

3. **When Wave 2 is approved**, scope is roughly:
   - `assessments` (unified Coursework table, `kind` enum, adjunct tables for kind-specific data)
   - `course_materials` (slides/notes/references, separate from assessments)
   - `ipg_attendance_sessions` + `ipg_attendance_records` (per-session, distinct from school-mode daily snapshots)
   - `ipg_leave_requests` + `ipg_leave_request_pensyarah_responses`
   - `ipg_discipline_cases` + `ipg_discipline_evidence` + `ipg_discipline_witnesses`
   - Open ambiguities to surface upfront: course material storage disk; adjunct table split per kind; whether to migrate or wipe existing thin Observation/Evaluation rows when Wave 3 expands them (user already said "wipe and regenerate" — apply that pattern to Wave 2 if existing seed conflicts).
   - All FKs hard, naming `created_by_user_id` / `updated_by_user_id`, idempotent seed via `firstOrCreate` / `updateOrCreate`.

---

## 9. Critical correctness invariants the user has set

Re-checking these on any future change:

- **No School-mode regression.** The user has been explicit multiple times: do not touch School-mode seed data, controllers, or schema except where IPG explicitly projects (only `school/students.blade.php` and `school/documents.blade.php` show the IPG trainee-tag and placement-letter strips, fed by `PracticumProjection`). Verify with `php artisan tinker` that school routes still resolve and seed counts match.
- **No identity duplication.** Trainees, pensyarah, and approved schools all reference canonical rows. The cross-mode bridge is `approved_practicum_schools` reading `schools.id`. Do not duplicate.
- **Per-Pensyarah scope (§6.2)** is enforced via `Placement::scopeVisibleTo($user)`. Any new practicum-related query must route through it. Pensyarah without `is_practicum_coordinator` sees only their assigned trainees; Penyelaras / IPG / BPG / MOE see campus-wide.
- **Trainee role data lockdown** — `trainee360()` and `transcripts()` already lock to `$user->trainee` for trainee role. Apply the same pattern to any new endpoint that exposes individual records (Wave 2 logbook, leave, etc.).

---

## 10. Environment state at handoff

- Laravel 12.x, PHP 8.2.12
- SQLite database at `database/database.sqlite`
- Vite asset build output in `public/build/` (CSS 67.20 KB / 11.98 KB gz)
- Tailwind v3 (active config at `tailwind.config.js`); `@tailwindcss/vite` v4 listed in package.json but unused (dead dep — flagged earlier, not removed)
- Seeded 10 users, 1 campus, 3 cohorts, 12 trainees, 5 pensyarah, 1 practicum window, 15 course offerings, 15 timetable sessions, 1 rubric (6 cats), 1 letter template, 1 approved school, 4 placements
- IPG mode browser-verifiable at `http://moe-laravel.weststar-dev.com/` (or `127.0.0.1:8000` via `php artisan serve`)
- Stale `public/hot` was removed earlier in the session — Vite assets served from build files unless `npm run dev` regenerates the file

---

*End of Wave 1 handoff.*

---
---

# Wave 2 Addendum — Hat 1 mini-LMS

> **Date:** 2026-04-30 (closed)
> **Author:** subsequent Claude session, continuing in the same project.
> **Read first (still authoritative):** `IPG_MODE_CHECKLIST.md`, `IPG_WORKFLOWS.md` at project root. The Wave 1 sections above are still load-bearing — Wave 2 layers on top, doesn't replace.

## TL;DR (Wave 2)

**Wave 2 (Hat 1 mini-LMS schema + seed) is COMPLETE and verified.** Five units shipped in order A → C → B → D → E → F; five new migrations applied as batches 6–10; 17 new model classes; 5 new `config/ipg.php` keys (well, 3 keys plus 2 promoted from constants); ~561 new IPG-mode rows seeded idempotently. Wave 1 + Wave 2 totals all stable across re-seeds.

The next session should:
1. Read both planning docs + this Wave 2 addendum.
2. Run `php artisan migrate:status` to confirm migrations through `2026_04_27_129000_create_ipg_discipline_schema` show `Ran`.
3. Run `php artisan db:seed --force` to ensure demo data populated; spot-check counts in §W2.1 below.
4. Wait for the user's go on Wave 3 before writing anything. Wave 3 scope is not yet defined; see "Open ambiguities for Wave 3+" below for likely candidates.

## W2.1 — Wave 2 status: what landed

### Unit A — Course Materials + Storage Config
- Migration `2026_04_27_125000_create_course_materials_schema.php` — 3 tables: `course_material_categories` (lookup), `course_materials`, `course_material_files`.
- Models: `CourseMaterialCategory`, `CourseMaterial`, `CourseMaterialFile`.
- New `config/ipg.php` file created. Keys: `pensyarah.max_trainee_load` (promoted from `Pensyarah::MAX_TRAINEE_LOAD` constant; constant removed; replaced with `Pensyarah::maxTraineeLoad()` static accessor), `uploads.course_materials.disk` / `max_file_size_mb` / `allowed_mime_types`.
- Storage: course materials use the `local` disk (storage/app/private — outside web root). Defaults to `hidden_draft` on create (the safer asymmetric pattern: accidental hide < accidental publish).
- Seed counts: 6 categories / 45 materials / 45 file stubs. Visibility split 30 visible / 15 hidden_draft.

### Unit C — IPG Attendance
- Migration `2026_04_27_126000_create_ipg_attendance_schema.php` — 2 tables: `ipg_attendance_sessions`, `ipg_attendance_records`.
- Models: `IpgAttendanceSession` (with status `recorded | cancelled`), `IpgAttendanceRecord` (with status `present | absent | late | excused_mc | excused_leave`).
- New config key: `attendance.late_edit_threshold_days` (default 3).
- Per-session granularity, distinct from school-mode's daily `attendance_snapshots`. `recorded_by_pensyarah_id` captures the substitute-lecturer case explicitly.
- Implicit cohort-wide enrollment: trainees in an offering's cohort are treated as enrolled (PISMP has no electives in v1).
- Seed counts: 60 sessions (45 recorded + 15 cancelled; 15 ad-hoc, 15 with substitute, 15 locked) / 180 records distributed 85/7/4/2/2 across 5 statuses.
- **Bug found and fixed:** Eloquent's bare `'date'` cast writes datetime format on insert, but `updateOrCreate` WHERE binds raw strings — caused unique-constraint failure on re-seed. Fixed by pinning to `'date:Y-m-d'`. See `feedback_eloquent_date_cast_pin.md` memory.

### Unit B — Assessments + Gradebook Columns + Online Test Question Bank
- Migration `2026_04_27_127000_create_assessments_schema.php` — 4 tables: `assessments`, `gradebook_columns`, `online_test_questions`, `online_test_question_options`.
- Models: `Assessment` (kind enum `assignment | tutorial | f2f_test | online_test`), `GradebookColumn` (kind enum `manual | participation | bonus`), `OnlineTestQuestion`, `OnlineTestQuestionOption`.
- **manual_grade carve-out:** offline-graded gradebook entries (manual / participation / bonus) are NOT in `assessments` — they live in the separate `gradebook_columns` table. Assessments table only carries true coursework with a submission/delivery lifecycle.
- F2F-test fields (`venue`, `allowed_materials`, `duration_minutes`) are nullable columns directly on `assessments`. Online-test fields (`attempts_allowed`, `result_release`) are also direct columns. Assignment/tutorial-specific config (submission types, group config, late penalty rules) lives in JSON `settings`.
- Late-penalty shape: `{"grace_hours": int, "per_day_pct": int, "max_pct": int, "after_max_action": "zero" | "reject"}`.
- Status: `draft | published | archived`. Archived is terminal in normal flow; unarchive requires IPG Admin override with audit.
- Seed counts: 60 assessments + 15 gradebook columns (75 total gradebook-feeding entries, ~75/17/8 published/draft/archived split) + 45 online-test questions across 8 of 15 tests (the other 7 are intentional empty-state shells) + 116 MCQ options (29 MCQ × 4, 2 multi-correct).
- Trainee submissions/attempts/responses are EXPLICITLY deferred (locked decision #13, still in force).

### Unit D — IPG Leave / MC Requests + Per-Pensyarah Course-Impact Responses
- Migration `2026_04_27_128000_create_ipg_leave_requests_schema.php` — 2 tables: `ipg_leave_requests`, `ipg_leave_request_pensyarah_responses`.
- Models: `IpgLeaveRequest` (status `submitted | approved | rejected | withdrawn` — NO `under_review`, leave decisions don't have an investigation phase), `IpgLeaveRequestPensyarahResponse` (response `acknowledge | approve_impact | object`).
- New config key: `leave.response_threshold_days` (default 7).
- **Critical schema decision:** the response row stores `pensyarah_id` EXPLICITLY (not derived through `course_offering->lecturer`). Preserves who actually responded under substitute / lecturer-reassignment scenarios. See `feedback_no_derived_identity_fks.md` memory.
- `auto_acknowledged` is an explicit bool flag (not inferred from null user_id) for the threshold-fallback case.
- **Migration was edited post-write** to add explicit short FK constraint names (`ilrpr_request_fk`, `ilrpr_offering_fk`, `ilrpr_responded_user_fk`) — auto-generated names overflowed MySQL's 64-char limit on this long-named table. See `feedback_short_fk_constraint_names.md` memory; check any future long-named IPG migrations the same way.
- Seed counts: 7 requests across 4 statuses + 5 kinds, 27 responses (3 auto_acknowledged, 14 approve_impact, 9 acknowledge, 4 object). 2 requests carry supporting MC documents.
- **Service-layer behavior NOT in this unit (forward-locked):** the auto-fill of attendance status from approved leave MUST act only on null/unset attendance statuses — must NEVER overwrite a status a lecturer has already manually marked. Captured in `IpgLeaveRequest` model docblock.

### Unit E — Discipline Categories + Incidents + Cases + Evidence + Witnesses
- Migration `2026_04_27_129000_create_ipg_discipline_schema.php` — 5 tables: `discipline_categories` (lookup, with `is_active` deactivation flag), `discipline_incidents` (peer-link entity), `ipg_discipline_cases`, `ipg_discipline_case_evidence`, `ipg_discipline_case_witnesses`.
- Models: `DisciplineCategory`, `DisciplineIncident`, `IpgDisciplineCase` (severity `minor | moderate | serious`; status `submitted | under_review | action_taken | dismissed`), `IpgDisciplineCaseEvidence`, `IpgDisciplineCaseWitness`.
- **Linking model:** when multiple Pensyarah file separate reports on the SAME incident, all cases carry the same `incident_id`. NOT a parent-child FK chain on cases. See `feedback_model_the_event_not_the_link.md` memory.
- Severity (incident-intrinsic) is orthogonal to `priority_flag` (queue treatment — auto-set true on serious; IPG Admin can override either direction).
- Filer identity: `filed_by_pensyarah_id` (workflow actor) + `created_by_user_id` (audit). NO `filed_by_user_id` — would be redundant.
- **Evidence is the most sensitive file type** in the system: defaults to `local` disk via column default, MIME stored verbatim from upload sniffing. Auth-gated downloads required (service-layer concern).
- Witness shape: single table with both internal (`witness_user_id` FK) and external (`witness_name` + `witness_contact` text) supported; service-layer enforces "at least one of user_id or name is set."
- Seed counts: 5 categories / 1 incident / 6 cases (1 of each status + 1 serious-priority + 1 linked pair via shared incident) / 5 evidence files / 4 witnesses (1 case has both internal AND external witnesses; 1 case has neither evidence nor witnesses).

### Unit F — Wave 2 Wrap (this section)
- No new schema. Cross-wave verification, documentation, memory cleanup.
- **Seed-ordering bug fixed in `DatabaseSeeder.php`:** the IPG ROLE TEST ACCOUNTS block (which wires `user_id` onto Pensyarah rows) was moved BEFORE the `IpgDemoSeeder` call. Previously the test accounts were wired AFTER demo data was generated, so on a fresh seed every audit FK on demo rows resolved to NULL.
- **Pensyarah 4 (Puan Nurul Syamiela) and Pensyarah 5 (Dr. Aminuddin Hassan) backfilled with user accounts** (`pensyarah.nurul@aiva.test`, `pensyarah.aminuddin@aiva.test`, password `password`). Eliminates the prior 10 NULL `responded_by_user_id` rows on Unit D leave responses; brings audit FKs to 100% populated on a fresh seed.
- 4 new feedback memories saved: date cast pin, no-derived-identity FKs, short FK names, model-the-event-not-the-link.

### Verified DB state at Wave 2 close

```
Wave 1: PracticumWindow=1 CourseOffering=15 TimetableSession=15
        ObservationRubric=1 PlacementLetterTemplate=1
        ApprovedPracticumSchool=1 Placements=4

Wave 2 Unit A: CourseMaterialCategory=6 CourseMaterial=45 CourseMaterialFile=45
Wave 2 Unit B: Assessment=60 GradebookColumn=15
               OnlineTestQuestion=45 OnlineTestQuestionOption=116
Wave 2 Unit C: IpgAttendanceSession=60 IpgAttendanceRecord=180
Wave 2 Unit D: IpgLeaveRequest=7 IpgLeaveRequestPensyarahResponse=27
Wave 2 Unit E: DisciplineCategory=5 DisciplineIncident=1 IpgDisciplineCase=6
               IpgDisciplineCaseEvidence=5 IpgDisciplineCaseWitness=4

Total IPG entities seeded across Wave 2: 561 rows
Total users now: 12 (was 10 pre-Wave-2; +2 backfill)
```

### School-mode regression check (Wave 2 close)

- Schools=1 Students=20 SchoolClasses=10 Enrollments=20 — all unchanged from Wave 1 close.
- `PracticumProjection::activeForSchool(1, today)` returns 2 visible trainees (active + confirmed placements); `lettersForSchool` returns 2 letters. Cross-mode projection still works.
- `Placement::scopeVisibleTo()` correctly scopes: `pensyarah@aiva.test` (regular Pensyarah) sees 1 placement (their assigned trainee); `ipg@aiva.test` (IPG Admin) sees all 4. Per-Pensyarah scope intact.

### Build state

Last clean asset build (end of Unit E): 67.20 KB CSS / 11.98 KB gz / 1.19s. **Note:** `node_modules/` was wiped at some point during the Unit F session (cause unknown — was present for 4 earlier successful builds). Wrap touched only PHP and config files, so the asset bundle is byte-identical to the Unit E baseline; no source-side change to verify. To restore local dev: `npm install && npm run build`.

## W2.2 — Decisions locked in during Wave 2

These join (do NOT replace) the Wave 1 locked decisions in §2 above. Do not relitigate without explicit user re-approval.

14. **Course material visibility default = `hidden_draft`.** Asymmetric risk — accidental publish is worse than accidental hide.
15. **Implicit cohort-wide enrollment for v1.** PISMP cohort = course enrollment unit. No `course_enrollments` join table; add when first elective-bearing program lands.
16. **Per-session attendance distinct from school-mode daily snapshots.** `ipg_attendance_*` tables; never reuse School mode's `attendance_snapshots`.
17. **`locked_at` columns store explicitly** (not computed from threshold) so IPG Admin can override with audit. Applies to `ipg_attendance_sessions.locked_at` and similar future patterns.
18. **manual_grade carved out of `assessments`** into separate `gradebook_columns` table. Assessments are coursework (with submission lifecycle); gradebook columns are offline-graded entries.
19. **Late penalty shape locked:** `{"grace_hours": int, "per_day_pct": int, "max_pct": int, "after_max_action": "zero" | "reject"}`.
20. **Group submission shape locked:** `{"enabled": bool, "min_size": int, "max_size": int, "formation": "lecturer_assigned" | "self_formed", "per_member_grade_adjustment": bool}`.
21. **Leave status enum has NO `under_review`.** Leave decisions don't have a meaningful investigation phase — discipline cases do. Status: `submitted | approved | rejected | withdrawn`.
22. **Pensyarah identity FK stored explicitly** on responses / actor columns; never derived through `course_offering->lecturer`. Same applies to all "who did X" columns.
23. **Discipline severity is orthogonal to priority_flag.** Severity describes the incident; priority describes queue treatment. Either can be flipped independently.
24. **Discipline case linking via shared `incident_id`** (entity-modeled), NOT parent-child FK or symmetric link table.
25. **Discipline category soft-deactivation** via `is_active=false` (preserves historical references); hard delete is restricted at the FK level.
26. **`response_threshold_at` snapshot** stored on each request row at creation time (not computed from config + created_at); IPG Admin can override per-request.
27. **Eloquent date casts pinned to `'date:Y-m-d'`** on any DATE column used as part of an `updateOrCreate` key. Bare `'date'` causes silent storage/lookup format mismatch.
28. **Explicit short FK / index names** required on long-named IPG tables to fit MySQL's 64-char limit.

## W2.3 — Test accounts (refreshed)

| Email | Role | Notes |
|---|---|---|
| `admin@aiva.test` | MOE superadmin | Unchanged from Wave 1. |
| `bpg@aiva.test` | BPG | Unchanged. |
| `ipg@aiva.test` | IPG Admin | Unchanged. |
| `kj.bm@aiva.test` | Ketua Jabatan (Bahasa Melayu) | Unchanged. |
| `penyelaras@aiva.test` | Penyelaras Praktikum | Unchanged. |
| `pensyarah@aiva.test` | Pensyarah (regular, Wong Kit Mun) | Unchanged. |
| `pensyarah.nurul@aiva.test` | **NEW** — Pensyarah (Puan Nurul Syamiela) | Backfilled in Unit F. |
| `pensyarah.aminuddin@aiva.test` | **NEW** — Pensyarah (Dr. Aminuddin Hassan) | Backfilled in Unit F. |
| `trainee@aiva.test` | Guru Pelatih | Unchanged. |
| `school@aiva.test` | School Admin (SMK Demo) | Unchanged. |
| `teacher@aiva.test` | Teacher | Unchanged. |

All passwords: `password`.

## W2.4 — Open ambiguities for Wave 3+

These are referenced or implied by Wave 1/2 work but explicitly NOT in v1 of any wave shipped so far. Any future planning round should treat these as unsettled:

- **Trainee-side workflows.** All Pensyarah workflows (Hat 1, Hat 2, Hat 3) have been schema-modeled. Trainee workflows (submit assignment, submit logbook, submit self-evaluation, take online test) are referenced extensively but not designed. This is the natural Wave 3 candidate.
- **Online test attempts/responses tables.** Schema deferred per locked decision #13. Will need: `online_test_attempts` (per trainee per attempt), `online_test_attempt_responses` (per question), grading roll-up.
- **Assignment submissions tables.** Same pattern — `assignment_submissions` (per trainee or per group), `assignment_submission_files`, `assignment_submission_grades`.
- **Trainee self-evaluation.** Referenced by W2.5 (Supervisor final practicum evaluation), explicitly deferred.
- **Service-layer wiring NOT done in any wave so far:**
  - Auto-acknowledge worker for `ipg_leave_request_pensyarah_responses` when `response_threshold_at` elapses.
  - Auto-excuse-from-approved-leave on attendance — must NOT overwrite manually-set statuses (locked).
  - Auto-set `priority_flag` on discipline cases when `severity=serious`.
  - Auto-lock attendance sessions past `late_edit_threshold_days`.
- **Permissions / role-based visibility refinement** beyond `Placement::scopeVisibleTo()` — most other models still default to "Superadmin sees everything" per Wave 1 decision. Will need scopes when finer-grained role visibility ships.
- **Notifications.** Still deferred (Wave 1 locked decision #7). Workflows reference notifications throughout.
- **v2 backlog (capture for later, NOT for any v1 wave):** `family` leave kind splits into `family + bereavement` per Malaysian academic context.

## W2.5 — Files modified during Wave 2

### New migrations (5)
- `database/migrations/2026_04_27_125000_create_course_materials_schema.php`
- `database/migrations/2026_04_27_126000_create_ipg_attendance_schema.php`
- `database/migrations/2026_04_27_127000_create_assessments_schema.php`
- `database/migrations/2026_04_27_128000_create_ipg_leave_requests_schema.php` (later edited for short FK names)
- `database/migrations/2026_04_27_129000_create_ipg_discipline_schema.php`

### New models (17)
`CourseMaterialCategory`, `CourseMaterial`, `CourseMaterialFile`,
`IpgAttendanceSession`, `IpgAttendanceRecord`,
`Assessment`, `GradebookColumn`, `OnlineTestQuestion`, `OnlineTestQuestionOption`,
`IpgLeaveRequest`, `IpgLeaveRequestPensyarahResponse`,
`DisciplineCategory`, `DisciplineIncident`, `IpgDisciplineCase`, `IpgDisciplineCaseEvidence`, `IpgDisciplineCaseWitness`.
(Note: 16 distinct, plus `Trainee` / `Pensyarah` / `CourseOffering` / `TimetableSession` were heavily extended — see "modified" list below.)

### Modified existing models
- `app/Models/Pensyarah.php` — `MAX_TRAINEE_LOAD` constant removed; `maxTraineeLoad()` static accessor reads `config('ipg.pensyarah.max_trainee_load')`. Added `recordedAttendanceSessions()`, `leaveRequestResponses()`, `filedDisciplineCases()` hasMany relations.
- `app/Models/CourseOffering.php` — Added `materials()`, `attendanceSessions()`, `assessments()`, `gradebookColumns()`, `leaveRequestResponses()` hasMany relations.
- `app/Models/TimetableSession.php` — Added `attendanceSessions()` hasMany. Docblock updated to note distinction from `IpgAttendanceSession` (recurring slot vs held instance).
- `app/Models/Trainee.php` — Added `attendanceRecords()`, `leaveRequests()`, `disciplineCases()` hasMany relations.

### Config
- `config/ipg.php` — **NEW FILE.** Keys: `pensyarah.max_trainee_load`, `attendance.late_edit_threshold_days`, `leave.response_threshold_days`, `uploads.course_materials.disk` / `max_file_size_mb` / `allowed_mime_types`.

### Seeder
- `database/seeders/IpgDemoSeeder.php` — heavily extended: 5 new seed methods (`seedCourseMaterialCategories`, `seedCourseMaterials`, `seedIpgAttendance`, `seedAssessments`, `seedDisciplineCategories`, `seedIpgLeaveRequests`, `seedIpgDisciplineCases`) plus helper methods. ~700+ new lines.
- `database/seeders/DatabaseSeeder.php` — IPG ROLE TEST ACCOUNTS block moved BEFORE `IpgDemoSeeder` call (seed-ordering fix). Added Pensyarah 4 + 5 user account creation.

### Documentation written
- `docs/CLAUDE_CODE_HANDOFF.md` — this Wave 2 addendum.
- 4 new feedback memory files in `~/.claude/projects/.../memory/`:
  - `feedback_eloquent_date_cast_pin.md`
  - `feedback_no_derived_identity_fks.md`
  - `feedback_short_fk_constraint_names.md`
  - `feedback_model_the_event_not_the_link.md`

## W2.6 — Critical correctness invariants (Wave 2 close)

In addition to all Wave 1 invariants in §9 above:

- **Idempotent reseed of all Wave 2 demo data.** All 5 seed methods use `updateOrCreate` / `firstOrCreate` keyed on natural keys. Verified by running `db:seed --force` twice; counts stable.
- **No School-mode regression.** Confirmed at Wave 2 close: school students/classes/enrollments unchanged; `PracticumProjection` still returns visible trainees and placement letters at host schools; trainee tag projection still works.
- **Per-Pensyarah scope (§6.2 from Wave 1) still intact.** Verified `pensyarah@aiva.test` sees scoped placements; IPG Admin sees full campus.
- **Audit FK columns 100% populated** on Wave 2 demo rows (after Unit F's Pensyarah 4+5 backfill and seed-ordering fix). The only NULL `responded_by_user_id` values on `ipg_leave_request_pensyarah_responses` are the 3 auto_acknowledged rows (correct by design).
- **Cross-mode FKs unchanged.** `approved_practicum_schools.school_id` is still the only IPG → School-mode hard FK.

*End of Wave 2 addendum.*
