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

*End of handoff.*
