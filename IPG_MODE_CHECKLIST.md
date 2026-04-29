# IPG Mode — Implementation Checklist

> **Context for Claude Code**: IPG (Institut Pendidikan Guru) mode has been scaffolded by copying School mode. This checklist is a **diff** against that scaffold — what to remove, rename, and add. Treat each section as a directive. Granularity is intentionally medium: feature/screen-level intent is specified, but schema design, route naming, component structure, and similar implementation choices are left to your judgment. Where intent is ambiguous, prefer the simplest implementation that preserves extensibility for v2.

---

## 0. Foundational Concepts (read first)

Before touching code, internalize these concepts — they shape every module below:

- **Mode**: IPG operates **independently** from School mode. The only touchpoint between modes is **Practicum** (see §6).
- **Scope (v1)**: Only the **PISMP** program. Architecture must allow other programs (PPISMP, KPLI, PDPLI) to be added later without refactoring.
- **Top entity**: `Campus` (not School). One IPG may have multiple campuses. The "ACTIVE SCHOOL" sidebar header becomes "ACTIVE CAMPUS".
- **Cohort**: Defined as the tuple `(Program, Major/Pengkhususan, Intake/Ambilan)`. Example: `PISMP + Matematik + Ambilan Jun 2024`. This replaces the School-mode notion of "class" (e.g., "5 Amanah").
- **Time structure**: **Semester-based**, not yearly terms. Schedules, timetables, transcripts, and the academic calendar all key off semesters.
- **Trainee** (Guru Pelatih): the "student" in IPG mode.
- **Pensyarah**: the "teacher" in IPG mode. May carry a flag `is_practicum_coordinator` which unlocks Penyelaras Praktikum capabilities (campus-wide practicum orchestration).
- **Visibility (v1)**: Superadmin sees everything. Role-based visibility filtering will be layered on later — do not over-engineer permissions now, but do not hardcode in ways that block future filtering.

---

## 1. Actors / Roles

Implement the following roles in IPG mode. Consolidations are intentional for v1.

- [ ] **IPG Admin** — consolidates Pengarah, Timbalan Pengarah, and administrative staff into one role with full campus access.
- [ ] **Ketua Jabatan** — scoped to a specific pengkhususan (major).
- [ ] **Pensyarah** — teaches courses, supervises trainees.
  - [ ] Add a flag/permission `is_practicum_coordinator` on the Pensyarah entity. When true, the user is treated as **Penyelaras Praktikum** and gains access to campus-wide Practicum orchestration. When false, they only see their assigned trainees within Practicum.
- [ ] **Guru Pelatih** (Trainee) — the student of the IPG.
- [ ] **BPG (Bahagian Pendidikan Guru)** — ministry-level role, parallel to the existing Ministry role for schools. Has its own ministry-level view (see §3).

> **Note**: School-side teachers are **NOT** mentors in this model. Mentorship/supervision during practicum is performed by IPG **Pensyarah**.

---

## 2. Modules to REMOVE from the IPG-mode scaffold

These modules were copied from School mode but do not apply to IPG. Remove them from the IPG sidebar, routes, and any IPG-mode-only code paths. Do **not** remove them from School mode.

- [ ] Remove **Face Enrollment**
- [ ] Remove **Live Monitor**
- [ ] Remove **Cameras**
- [ ] Remove **Safety**
- [ ] Remove **Relationships**
- [ ] Remove the entire **SAFETY & INCIDENTS** sidebar section header (since all its children are removed).

---

## 3. Modules to RENAME (semantic + label changes)

For each of the following, update the sidebar label and adapt the underlying semantics. Where the original module's data model still fits, reuse it; otherwise adapt schema to the new concept.

- [ ] **`ACTIVE SCHOOL` → `ACTIVE CAMPUS`** (sidebar header).
- [ ] **`MINISTRY` → `MINISTRY (BPG)`** — the ministry view is BPG-specific in IPG mode.
  - [ ] **`Schools` → `Campuses`** — list of all IPG campuses across the BPG view.
  - [ ] `Overview` and `Trends` retain their labels; data is IPG-flavored.
- [ ] **`SCHOOL SYSTEM` → `CAMPUS SYSTEM`**.
  - [ ] **`Schedule` (Jadual Kelas) → `Academic Calendar`** — semester dates, exam weeks, practicum windows, registration periods.
- [ ] **`Grades & Classes` → `Grades & Cohorts`** — "Class" becomes "Cohort" defined as `(Program, Major, Intake)`.
- [ ] **`Students` → `Trainees`** (Guru Pelatih).
- [ ] **`Student 360` → `Trainee 360`** — academic record, practicum history, supervisor notes, hostel info, co-curriculum participation, research project status.

---

## 4. Modules to KEEP largely as-is (semantic adaptation only)

These modules carry over with only minor semantic adjustments (e.g., "student" → "trainee", year-based → semester-based where relevant). Do not redesign their UX.

- [ ] `Overview` (Campus-level)
- [ ] `Enrollment` — semester-based intake of new trainees
- [ ] `Timetables` — semester-based course schedules
- [ ] `Leaves`
- [ ] `Assistance`
- [ ] `Chat`
- [ ] `Documents`
- [ ] `Events`
- [ ] `Surat Cuti / MC`
- [ ] `Laporan Disiplin`
- [ ] `Attendance`
- [ ] `Reports`
- [ ] `Settings`

---

## 5. NEW modules to ADD under existing sections

These do not exist in School mode and must be built from scratch.

### Under `ACADEMICS`:
- [ ] **Transcripts** — semester-based academic records, GPA/CGPA per semester, course-level grades. Per-trainee view + cohort-level view for IPG Admin.
- [ ] **Co-curriculum** (Kokurikulum) — mandatory in PISMP. Track trainee participation in co-curricular activities, units/credits earned, evaluations.
- [ ] **Research** (Penyelidikan) — final-year research project tracking. Per-trainee: title, supervisor, milestones, submission status, evaluation.

### Under `TRAINEES`:
- [ ] **Hostel** (Asrama) — room assignments, hostel attendance, hostel discipline records, capacity/occupancy view.

---

## 6. NEW SECTION: `PRACTICUM`

This is the only point of interconnection between IPG mode and School mode. Place it as its **own top-level sidebar section** between `ACADEMICS` and `TRAINEES`.

### Sub-modules to build:

- [ ] **Placements** — manage which trainee is placed at which host school, for which window, teaching what subjects/levels. Initiates a placement record that is also visible on the host school's side (see §6.1).
- [ ] **Supervisors** — assign Pensyarah to trainees as supervisor (Pensyarah Penyelia). One Pensyarah may supervise many trainees. Penyelaras Praktikum manages assignments campus-wide.
- [ ] **Observations** — scheduled and completed classroom observation visits by the assigned Pensyarah. Each observation captures date, lesson observed, notes, and rubric scoring.
- [ ] **Evaluations** — formal practicum grading. Rubric-based. Outcome contributes to the trainee's overall academic record (link to Transcripts).
- [ ] **Logbook / Reflection** — trainee-submitted weekly reflections; Pensyarah reviews and comments.
- [ ] **School Coordination** — generation of placement letters to host schools, tracking principal acknowledgements, basic correspondence log.

### 6.1. School-mode side of Practicum (interconnection — option B)

When a trainee is placed at a host school via the Practicum module, the host school's **existing School-mode interface** must reflect the placement minimally:

- [ ] The trainee appears in the host school's relevant Students/staff list with a **"trainee" tag**.
- [ ] The associated **placement letter document** appears in the school's Documents area.
- [ ] **Nothing more** — do not integrate the trainee into host-school timetables, attendance flows, or grade systems. Heavy supervision/evaluation work stays inside IPG mode.

### 6.2. Permissions within Practicum

- [ ] A Pensyarah **without** the `is_practicum_coordinator` flag sees only the trainees they are assigned to supervise (across Observations, Evaluations, Logbook).
- [ ] A Pensyarah **with** the flag (Penyelaras Praktikum) sees the full Practicum module campus-wide: all placements, all supervisor assignments, all coordination correspondence.
- [ ] IPG Admin and Superadmin always see everything.
- [ ] For v1, defer fine-grained role visibility for non-Pensyarah roles — Superadmin-default is acceptable.

---

## 7. Final IPG sidebar (target state)

After all of the above, the IPG sidebar should look like:

```
ACTIVE CAMPUS
[Campus Name]

MINISTRY (BPG)
- Overview
- Campuses
- Trends

CAMPUS SYSTEM
- Overview
- Academic Calendar

ACADEMICS
- Grades & Cohorts
- Enrollment
- Timetables
- Transcripts          [NEW]
- Co-curriculum        [NEW]
- Research             [NEW]

PRACTICUM              [NEW SECTION]
- Placements
- Supervisors
- Observations
- Evaluations
- Logbook / Reflection
- School Coordination

TRAINEES
- Trainees
- Trainee 360
- Leaves
- Assistance
- Hostel               [NEW]

COMMUNICATION
- Chat
- Documents

MANAGEMENT
- Events
- Surat Cuti / MC
- Laporan Disiplin
- Attendance
- Reports
- Settings
```

---

## 8. Cross-cutting implementation notes

- [ ] **Mode separation**: Ensure no IPG-mode change leaks into School mode. School mode must continue to function exactly as before. The sidebar, routes, and data scoping must be cleanly mode-aware.
- [ ] **Cohort modeling**: Model Cohort as a first-class entity keyed by `(program, major, intake)` rather than a free-text class name, so future programs (PPISMP/KPLI/PDPLI) plug in without refactoring.
- [ ] **Semester modeling**: Introduce a `Semester` entity (campus-scoped) referenced by Timetables, Transcripts, Enrollment, and the Academic Calendar.
- [ ] **Trainee = User**: A trainee may also exist as a "trainee-tagged" reference inside a host school during placement. Keep the canonical trainee record in IPG mode; the school-side appearance is a projection, not a duplicate identity.
- [ ] **Practicum window**: The "trainee" tag and placement document on the school-side should appear/disappear based on the active practicum window (start/end dates on the placement record).
- [ ] **Extensibility hooks** (do not implement, but do not preclude):
  - Multiple programs beyond PISMP
  - Practicum phases (Fasa 1, 2, 3, Internship)
  - Role-based visibility refinement beyond Pensyarah's coordinator flag
  - Cooperating-teacher (Guru Pembimbing) tracking on the school side

---

## 9. Out of scope for v1 (explicitly deferred)

To prevent scope creep, the following are **not** in v1 and should not be built now:

- Programs other than PISMP.
- Practicum phase distinctions (treat practicum as a single concept).
- Fine-grained per-role visibility filtering (beyond the Pensyarah coordinator flag).
- Formal Guru Pembimbing (cooperating teacher) role at host schools.
- School-side deep integration of trainees (timetables, attendance, grades).

---

## 10. Suggested execution order

1. Confirm scaffold state and inventory what was copied from School mode.
2. Apply removals (§2).
3. Apply renames (§3).
4. Add new modules under existing sections (§5).
5. Build the Practicum section (§6), starting with Placements and Supervisors (the foundation other sub-modules depend on).
6. Wire the school-side projection (§6.1) last — it is the trickiest cross-mode piece.
7. Verify the final sidebar matches §7.
8. Smoke-test School mode to confirm no regression.
