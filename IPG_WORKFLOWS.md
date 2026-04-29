# IPG Mode — Workflows

> **Companion to `IPG_MODE_CHECKLIST.md`**. The checklist defines *what* to build (modules, removals, renames, additions). This document defines *how each actor uses the system to accomplish their goals* (workflows).
>
> **Granularity**: Medium. Each workflow specifies trigger, pre-conditions, screen-by-screen flow, inputs/outputs, handoffs, success state, and edge cases. Schema, route names, and component structure are left to implementation judgment.
>
> **Scope of this document (v1)**: Currently covers **Pensyarah (Lecturer)** workflows only. Other actors (Guru Pelatih, Ketua Jabatan, IPG Admin, BPG) to be added in subsequent revisions.

---

## Foundational concept: Pensyarah's three hats

A Pensyarah may wear up to three hats in the system. Workflows differ significantly by hat. The UI must make it obvious which hat a Pensyarah is currently wearing and which workflows are even available to them.

1. **Lecturer** — teaches courses, manages cohorts academically. Every Pensyarah has this hat.
2. **Supervisor** — supervises specific trainees during practicum. Active when assigned via W3.3.
3. **Penyelaras Praktikum** — campus-wide practicum orchestration. Active when `is_practicum_coordinator = true`.

A Pensyarah wears hat 1 always, hats 1+2 when assigned trainees, and all three if also flagged as coordinator.

---

## Workflow index

### Hat 1: Lecturer
- W1.1 — Take attendance for a class session
- W1.2 — Record/update grades for a course assessment
- W1.3 — View own teaching timetable
- W1.4 — Review and respond to a trainee's leave/MC request for their course
- W1.5 — Submit a disciplinary report (Laporan Disiplin)
- W1.7 — Manage course materials & coursework (mini-LMS, see sub-workflows)
  - W1.7.1 — Upload and organize course materials
  - W1.7.2 — Create and assign an assignment
  - W1.7.3 — Create and assign a tutorial
  - W1.7.4 — Create and schedule a face-to-face test
  - W1.7.5 — Create and configure an online test
  - W1.7.6 — Grade submitted coursework
  - W1.7.7 — Monitor a coursework item
- W1.8 — Supervise a research project
- W1.9 — Evaluate co-curriculum participation

### Hat 2: Supervisor
- W2.1 — View list of assigned trainees for the current practicum window
- W2.2 — Schedule an observation visit
- W2.3 — Conduct an observation and submit the observation report
- W2.4 — Review and comment on a trainee's logbook / reflection
- W2.5 — Submit the final practicum evaluation
- W2.6 — Coordinate with host school

### Hat 3: Penyelaras Praktikum
- W3.1 — Plan a practicum window
- W3.2 — Assign trainees to host schools (placement)
- W3.3 — Assign supervisors to trainees
- W3.4 — Generate and dispatch placement letters
- W3.5 — Monitor campus-wide practicum progress
- W3.6 — Handle exceptions

### Lifecycle dependencies

The Penyelaras Praktikum's full arc:
**W3.1** (plan window) → **W3.2** (place trainees) → **W3.3** (assign supervisors) → **W3.4** (dispatch & confirm) → **W3.5** (monitor ongoing) → **W3.6** (handle exceptions as they arise)

The Supervisor's full arc:
**W2.1** (see assigned trainees) → **W2.2** (schedule observations) → **W2.3** (conduct observations) → **W2.4** (review logbook ongoing) → **W2.6** (coordinate with school as needed) → **W2.5** (submit final evaluation)

---

## Cross-cutting decisions affecting multiple workflows

These were confirmed during planning and apply across workflows:

- **BPG-configured templates** (locked at campus level):
  - Practicum observation rubric (W2.3, W2.5)
  - Placement letter template (W3.4)
- **IPG Admin-configured registries**:
  - Approved practicum schools list (W3.2)
- **Plagiarism / similarity checking**: **NOT in v1**. Pensyarah grades manually; suspected plagiarism is handled via W1.5 (Laporan Disiplin) as academic dishonesty.
- **Visibility (v1)**: Superadmin sees everything; per-role visibility refinement deferred. However, certain Hat-based access rules ARE in scope from v1:
  - Pensyarah without `is_practicum_coordinator` flag: in Practicum, sees only their assigned trainees
  - Pensyarah with the flag: sees campus-wide Practicum module

---

# Hat 1: Lecturer Workflows

## W1.1 — Take attendance for a class session

**Module**: Attendance (or Timetables → session detail)
**Frequency**: Every class session — high frequency, must be fast

### Trigger
A scheduled class session begins; Pensyarah needs to record who's present.

### Pre-conditions
- A timetabled session exists for today
- Pensyarah is assigned to that session
- The cohort has enrolled trainees

### Screen flow
1. **Entry** — Pensyarah opens Attendance (or taps today's session from timetable). Today's sessions ordered by time; current/next session prominent.
2. **Open session** — Trainee list with each row showing name, photo, status toggle defaulting to `Present`.
3. **Mark exceptions** — Tap trainees who are absent/late: `Absent`, `Late`, `Excused (MC)`, `Excused (Leave)`. Approved Surat Cuti / MC for today auto-defaults trainee to `Excused`.
4. **Submit** — Bulk submit. Default-everyone-present pattern is intentional.

### Outputs
Attendance records, one per trainee per session.

### Handoffs
- Trainee 360: cumulative attendance feeds trainee's record
- Reports: campus-wide attendance analytics
- IPG Admin / Ketua Jabatan: chronic absenteeism flags

### Edge cases
- **Late submission**: editing allowed for N days (configurable, default 3) with a flag
- **Cancelled session**: mark entire session as `Cancelled` with reason; no individual records
- **Substitute lecturer**: another Pensyarah can take attendance even if not the primary; record who actually took it

---

## W1.2 — Record/update grades for a course assessment

**Module**: Grades & Cohorts
**Frequency**: Several times per semester per course

### Trigger
An assessment is complete and marked; Pensyarah enters scores.

### Pre-conditions
- An assessment exists in the course's grading scheme
- Trainees enrolled in the cohort

### Screen flow
1. **Entry** — Open Grades & Cohorts, pick course, open gradebook (trainees as rows, assessments as columns).
2. **Enter scores** — Inline cell edit OR bulk CSV import.
3. **Validation** — Scores within max range; missing trainees flagged; late submissions noted.
4. **Save and publish** — Save as draft (private) or publish (trainees see scores and feedback).

### Outputs
Grade records per trainee per assessment, contributing to course grade and transcript.

### Handoffs
- Trainee: notification on publish
- Transcripts: course grades roll up at semester-end
- Ketua Jabatan: visibility into grade distributions

### Edge cases
- **Grade dispute**: trainee flags for review; appears in Pensyarah's inbox
- **Late submission grading**: assessment links to W1.7 assignment record (if applicable); inherits late penalty rules
- **Curve / moderation**: allow Pensyarah to apply a curve as a separate adjustment with reason
- **Mid-semester withdrawal**: withdrawn trainees stay in gradebook for completed work

---

## W1.3 — View own teaching timetable

**Module**: Timetables
**Frequency**: Daily reference

This is primarily a read view, not a workflow. Implementation notes:
- Lecturer-filtered view of campus timetable (their sessions only)
- Day / week / semester views
- Click into a session shows: cohort, room, materials links (W1.7.1), quick action to take attendance (W1.1)
- Calendar export (iCal) is nice-to-have

---

## W1.4 — Review and respond to a trainee's leave/MC request for their course

**Module**: Surat Cuti / MC (or Leaves) — filtered to Pensyarah's courses
**Frequency**: As-needed, low frequency per Pensyarah

### Trigger
A trainee submits a leave/MC request affecting Pensyarah's course (missed class, extension, test absence).

### Pre-conditions
- Trainee has submitted a leave/MC request
- Request is routed to this Pensyarah (overlaps their course schedule)

### Screen flow
1. **Notification** — Pensyarah notified of pending request.
2. **Review** — Open request: trainee, dates, reason category (Medical / Personal / Family / Co-curricular / Other), supporting document, affected sessions/assessments, trainee's note.
3. **Respond** — Three options:
   - **Acknowledge** — note awareness; doesn't approve/deny but informs central process
   - **Approve impact** — approve the trainee's request to miss class and/or get extension; specify conditions
   - **Object** — flag a concern; central leave processor (IPG Admin) sees the objection
4. **Auto-effects on approval** — Affected attendance auto-set to `Excused`; affected deadlines update for that trainee; response logged in request history.

### Authority note
The Pensyarah doesn't *approve* the leave itself — that's IPG Admin's call. The Pensyarah's role is course-specific impact: do they grant the extension, accept the absence.

### Edge cases
- **Multiple Pensyarah affected**: each responds independently for their own course
- **Pensyarah doesn't respond in time**: after configurable threshold, treated as `Acknowledged` (silent acceptance)
- **Overlapping requests**: show context of trainee's existing approved leaves

---

## W1.5 — Submit a disciplinary report (Laporan Disiplin)

**Module**: Laporan Disiplin
**Frequency**: Rare per Pensyarah, but important when needed

### Trigger
Trainee has done something requiring formal disciplinary documentation — repeated lateness, academic dishonesty, disrespect, etc.

### Pre-conditions
- Trainee exists in a cohort
- Pensyarah has authority to file (typically all Pensyarah do)

### Screen flow
1. **Entry** — Open Laporan Disiplin → "+ New Report".
2. **Form**:
   - Trainee (searchable selector)
   - Date and time of incident
   - Category (configurable list — e.g., Akademik, Kelakuan, Kehadiran, Etika, Lain-lain)
   - Severity (Minor / Moderate / Serious)
   - Description (narrative)
   - Evidence (optional attachments)
   - Witnesses (optional)
   - Recommended action (advisory only — IPG Admin makes final call)
3. **Submit** — Report enters `Submitted` state; IPG Admin and Ketua Jabatan auto-notified; trainee NOT auto-notified (notification happens after IPG Admin reviews — protects fair process).
4. **Track** — Pensyarah views status: `Submitted` → `Under Review` → `Action Taken` / `Dismissed`.

### Handoffs
- IPG Admin: review and action queue
- Ketua Jabatan: notification (their student is the subject)
- Trainee: notified only after IPG Admin processes

### Edge cases
- **Retraction**: allowed before review starts, blocked after
- **Multiple reports for the same incident** (two Pensyarah witness): allow linking; IPG Admin reviews together
- **Serious severity**: auto-escalates with priority flag

---

## W1.7 — Manage course materials & coursework

A mini-LMS within IPG mode. Comprises seven sub-workflows. Architecture: a unified `Coursework` concept with subtypes (assignment, tutorial, F2F test, online test) sharing a common shape but specializing where needed. Materials live separately from coursework but in the same course workspace.

### W1.7.1 — Upload and organize course materials

**Module**: Grades & Cohorts → course detail → Materials tab
**Frequency**: Throughout the semester, front-loaded at start

#### Trigger
Pensyarah needs to share resources with a cohort — slides, notes, references, past year exams.

#### Pre-conditions
- Pensyarah is assigned to teach this course in this cohort
- Course exists in the semester's schedule

#### Screen flow
1. **Entry** — Open course → Materials. Organized by category (configurable, with defaults: Course Notes, Slides, Past Year Exam Papers, References, Worksheets, Other).
2. **Upload** — Tap "+ Add Material" within a category. Fields: title, description (optional), file(s) (multi-upload), category, visibility (`Visible to Trainees` / `Hidden Draft`), optional topic/week tag.
3. **Manage** — Reorder, edit, hide, or remove at any time. Hidden = draft state, useful for preparing future-week content.

#### Edge cases
- **File size limits**: configurable; sane default (e.g., 50MB per file)
- **Bulk upload**: drag-and-drop multiple files at once
- **Reuse from previous semester**: allow copying materials from a prior semester's instance of the same course (significant time-saver)
- **Material updates after publish**: replacement tracked; trainees not auto-notified of replacements

---

### W1.7.2 — Create and assign an assignment

**Module**: Grades & Cohorts → course detail → Coursework tab
**Frequency**: A handful per semester per course

#### Trigger
Pensyarah is ready to assign work with a deadline.

#### Pre-conditions
- Course exists; Pensyarah is the assigned lecturer

#### Screen flow
1. **Entry** — Coursework → "+ New Coursework" → "Assignment".
2. **Configure**:

   *Basic*: title, description (rich text), attachments (templates, references)

   *Submission*:
   - Submission types (multi-select): file upload / text response / link / combination
   - File constraints (types, max size, max number)
   - **Group submission toggle**: if enabled, configure group size range (min–max), Pensyarah-defined OR trainee self-formed groups; one submission per group, grade applies to all (with optional per-member adjustment)

   *Deadline*:
   - Open date (when trainees can start)
   - Due date
   - Late submission policy: `Not Allowed` / `Allowed with Penalty` / `Allowed without Penalty`
   - If with penalty: rules (e.g., -10% per day, max -50%)

   *Grading*:
   - Total marks
   - Rubric (optional, Pensyarah-defined for this assignment)
   - Weight in final course grade (links to course grading scheme)

3. **Save / publish** — Draft (Pensyarah-only) or Publish (visible to trainees on/after open date).

#### Outputs
Assignment record visible from open date onward.

#### Handoffs
- Trainees: notification when published or on open date
- Gradebook (W1.2): assignment column auto-added with weight
- W1.7.7 monitoring: dashboard tracks submission status

#### Edge cases
- **Edit after publish**: minor edits allowed; structural changes (deadline, grading) require explicit confirmation and trainee notification
- **Cancellation**: explicit delete, notify trainees; existing submissions archived
- **Group disputes**: per-member grade adjustment with flag; formal peer-evaluation deferred to v2

---

### W1.7.3 — Create and assign a tutorial

**Module**: Grades & Cohorts → course detail → Coursework tab
**Frequency**: More frequent than assignments (often weekly)

Tutorials use the **same form as assignments** with these differences:
- Lower default weight in course grade, OR ungraded/formative toggle
- Group submissions less common but supported
- Often `Completed` / `Not Completed` rather than scored out of N

Implementation: treat tutorial as an assignment subtype (`coursework_type: 'tutorial'`) with different defaults. Same form, different labelling and gradebook treatment.

Edge cases: same as W1.7.2.

---

### W1.7.4 — Create and schedule a face-to-face test

**Module**: Grades & Cohorts → course detail → Coursework tab
**Frequency**: A handful per semester (mid-term, final, quizzes)

#### Trigger
Pensyarah needs to schedule an in-person test/exam.

#### Screen flow
1. **Entry** — Coursework → "+ New Coursework" → "Face-to-Face Test".
2. **Configure**:
   - Title (e.g., "Mid-term Exam", "Quiz 2")
   - Description / instructions
   - Date and time
   - Duration (minutes)
   - Venue (room/hall)
   - Allowed materials (e.g., "Open book", "Calculator allowed")
   - Total marks
   - Weight in final course grade
   - Attachments (revision guide, formula sheet)
3. **Notify** — On publish: trainees notified, test appears in trainee's calendar/timetable, gradebook row created for manual entry post-test.
4. **Post-test** — Pensyarah enters scores via W1.2 (gradebook) or per-test scoring screen.

#### Edge cases
- **Reschedule**: allowed; auto-notifies trainees
- **Make-up tests**: per-trainee scheduling for those with approved leave during original date
- **Venue clashes**: cross-checking is nice-to-have; defer to v2 unless timetabling already handles it

---

### W1.7.5 — Create and configure an online test

**Module**: Grades & Cohorts → course detail → Coursework tab
**Frequency**: A few per semester

**v1 scope**: MCQ + short answer only, time limit, auto-grade MCQ, manual-grade short answer.

#### Pre-conditions
- Course exists; Pensyarah assigned

#### Screen flow
1. **Entry** — Coursework → "+ New Coursework" → "Online Test".
2. **Configure metadata**:
   - Title, description, instructions
   - Total marks, weight in course grade
   - Available window (open/close date-time)
   - Duration (time limit once started, in minutes)
   - Attempts allowed (default: 1)
   - Result release: `Immediately after submission` / `After test window closes` / `Manual release by Pensyarah`
3. **Build questions** — Two types:

   *MCQ*: question text (rich text + image), 2–6 options, correct answer(s) (single or multi-correct), marks, optional explanation shown after grading.

   *Short Answer*: question text, suggested answer (Pensyarah-only, used during manual grading), marks.

4. **Order and review** — Reorder, preview as trainee, save as draft or publish.
5. **Trainees take the test** — *Trainee workflow, defined later.*
6. **Grading** — MCQ auto-grades on submission; short answers surface in Pensyarah's grading queue. Pensyarah scores each short answer with optional inline feedback. Final score = MCQ auto + short answer manual; system aggregates.

#### Edge cases
- **Connection lost mid-test**: allow resume within time limit (track elapsed time, not wall clock)
- **Attempt expired**: auto-submit whatever was answered
- **Unanswered short answers**: count as 0
- **Question edited after publication**: block; version the test instead
- **Re-grade requests**: trainee flags a question; appears in Pensyarah's queue

---

### W1.7.6 — Grade submitted coursework

**Module**: Grades & Cohorts → course detail → Coursework → submissions
**Frequency**: After each coursework deadline

#### Trigger
Submissions exist (assignment, tutorial, online test short answers); Pensyarah grades.

#### Screen flow
1. **Entry** — Open coursework item → Submissions view. List shows: trainee/group, status (`Submitted` / `Late` / `Missing`), timestamp, grade status (`Ungraded` / `Graded` / `Returned`).
2. **Filter and prioritize** — By ungraded, late, or specific cohort.
3. **Open submission** — Detail shows: trainee/group ID, submission contents (file preview, text inline, link rendered), timestamp + lateness flag, rubric (if defined), score input + feedback area, per-member adjustment (groups).
4. **Grade**:
   - Enter score (rubric-guided or freeform)
   - Add feedback comment
   - Optional: annotate the submission directly (PDF annotation if v1 budget allows; otherwise comment-only)
   - Groups: same grade for all OR split per member with reasons
5. **Return** — Mark as `Graded` (saves, private) or `Return to Trainee` (publishes grade and feedback). Returning is intentional — Pensyarah grades privately and publishes in batches.

#### Outputs
Graded submission records; gradebook entries; feedback visible to trainees on return.

#### Handoffs
- Trainee: notification on return with score and feedback
- Gradebook: scores feed in automatically

#### Edge cases
- **Re-grade after return**: allowed but tracked; trainee notified of change
- **Bulk return**: grade all and return at once
- **Disputed grades**: trainee flags via "Request Review"; appears in Pensyarah's queue
- **Suspected plagiarism**: handled manually; if confirmed, escalate via W1.5 (Laporan Disiplin) as academic dishonesty

---

### W1.7.7 — Monitor a coursework item

**Module**: Grades & Cohorts → course detail → Coursework → item detail
**Frequency**: Ongoing during a coursework's active period

#### Trigger
Pensyarah wants to see how a coursework is progressing.

#### Screen flow
1. **Entry** — Open coursework item; default view is dashboard:
   - Submission counts: `Submitted X / Y`, `Late: Z`, `Missing: W`
   - Time-to-deadline indicator
   - For graded items: score distribution histogram, mean, median, pass rate
2. **Drill in**:
   - Tap "Missing" → list of non-submitters; quick action to nudge (notification)
   - Tap "Late" → list with lateness duration; useful for consistent late penalty
   - Tap any score band → trainees in that band
3. **Take action**:
   - Nudge missing/late submitters
   - Extend deadline (per-trainee or class-wide) with reason
   - Open submission for grading (links to W1.7.6)

#### Edge cases
- **Pre-deadline view**: most useful for early intervention
- **Post-deadline view**: focuses on grading throughput and exceptions

---

## W1.8 — Supervise a research project

**Module**: Research (under ACADEMICS — see checklist §5)
**Frequency**: Throughout the trainee's final-year project; regular check-ins

### Trigger
Pensyarah is assigned as research supervisor for a final-year trainee.

### Pre-conditions
- Trainee is in their research-project semester
- Pensyarah is formally assigned (assignment workflow likely with Ketua Jabatan — to define later)

### Screen flow
1. **Entry** — Research module → My Supervisees (list of trainees they supervise).
2. **Trainee project view** — Tap a trainee. Project detail:
   - Project title, abstract, area
   - Milestones (proposal, lit review, methodology, data collection, analysis, draft, final, defense) with status and target dates
   - Submitted documents per milestone
   - Comment thread between trainee and supervisor
   - Final evaluation form (locked until project complete)
3. **Ongoing supervision** — Review submitted documents per milestone, comment / request revisions, mark milestones complete, schedule supervision meetings (light — just dates and notes).
4. **Final evaluation** — At project end, supervisor submits rubric-based evaluation feeding the trainee's transcript.

### Edge cases
- **Co-supervisor**: allow a second supervising Pensyarah; both have comment access; primary submits final evaluation
- **Supervisor change mid-project**: workflow similar to W3.6 supervisor reassignment
- **Defense / viva**: scheduling and panel evaluation — defer detailed flows to a future iteration unless critical for v1

---

## W1.9 — Evaluate co-curriculum participation

**Module**: Co-curriculum (under ACADEMICS — see checklist §5)
**Frequency**: Per semester per advised activity

### Trigger
Pensyarah is assigned as advisor for a co-curricular unit (Persatuan Matematik, Kelab Sukan); end-of-semester evaluation due.

### Pre-conditions
- Pensyarah assigned as advisor (assignment workflow likely with IPG Admin or Ketua Jabatan — to define later)
- Trainees enrolled in the unit
- Semester is at evaluation phase

### Screen flow
1. **Entry** — Co-curriculum → My Activities (units they advise).
2. **Unit detail** — Enrolled trainees, attendance records across the semester's activities, trainee-submitted reflections/portfolios, evaluation form per trainee.
3. **Evaluate** — Per trainee, score on rubric (attendance, participation, leadership, contribution) and submit. Aggregated into co-curriculum credits/units feeding the transcript.

### Edge cases
- **Attendance tracking**: similar to W1.1 but for co-curricular events; the Co-curriculum module needs its own attendance flow — note for the build
- **Leadership roles**: may earn additional credits; configurable per IPG Admin policy

---

# Hat 2: Supervisor Workflows

## W2.1 — View list of assigned trainees for the current practicum window

**Module**: Practicum → My Trainees (default Practicum landing for non-coordinator Pensyarah)
**Frequency**: Frequent — Pensyarah's home base during active practicum

### Trigger
Pensyarah opens Practicum to see current supervision load and act on it.

### Pre-conditions
- At least one trainee assigned via W3.3
- A practicum window is active or recently completed

### Screen flow
1. **Entry** — Non-coordinator Pensyarah lands on My Trainees. (Coordinators land on W3.5 dashboard but can switch to My Trainees view.)
2. **Trainee list** — Each row: trainee name + photo, host school + window dates, subjects/levels taught, status badges (observations done e.g., "1 of 3", logbook latest "4 days ago", evaluation status), attention indicator if needed.
3. **Drill in** — Tap a trainee → trainee-scoped practicum view with tabs:
   - **Overview** — placement details, contact info, host school info, schedule
   - **Observations** — past + scheduled (entry to W2.2 / W2.3)
   - **Logbook** — trainee's reflection submissions (entry to W2.4)
   - **Evaluation** — final evaluation form (entry to W2.5; locked until window-end approaches)
   - **Documents** — placement letter, lesson plans uploaded by trainee, etc.

### Edge cases
- **Zero assigned trainees**: friendly empty state
- **Trainee reassigned away**: archived view available (read-only) showing observations conducted before reassignment

---

## W2.2 — Schedule an observation visit

**Module**: Practicum → trainee detail → Observations tab
**Frequency**: 2–4 times per trainee per practicum window

### Trigger
Pensyarah needs to plan a classroom observation.

### Pre-conditions
- Trainee placement is `Confirmed`
- Practicum window is active

### Screen flow
1. **Entry** — From trainee's Observations tab, "+ Schedule Observation".
2. **Form**:
   - Date and time slot
   - Subject and topic (lesson to be observed; Pensyarah may not know in advance — allow trainee to fill in)
   - Class/level (e.g., Tahun 4 Bestari)
   - Mode — In-person / Remote
   - Notes for trainee (e.g., "Please prepare full lesson plan")
3. **Save and notify** — Scheduled observation record created; trainee notified (can confirm, request reschedule, fill in lesson topic). Host school NOT auto-notified — trainee coordinates locally with cooperating teacher.

### Outputs
Scheduled (not yet conducted) observation. Entry point for W2.3.

### Edge cases
- **Trainee requests reschedule**: notification flow back to Pensyarah, who edits the schedule
- **Conflicting visits** (same time, two different trainees, two different schools): warn but don't block — Pensyarah may know logistics permit
- **Schedule beyond practicum window**: block with clear error

---

## W2.3 — Conduct an observation and submit the observation report

**Module**: Practicum → Observations
**Frequency**: Multiple times per practicum window per trainee (typically 2–4 visits)

### Trigger
A previously scheduled observation date has arrived (W2.2), or Pensyarah is at the host school and decides to log an unplanned observation.

### Pre-conditions
- Practicum placement record exists for the trainee (W3.2)
- Pensyarah is the assigned supervisor (W3.3)
- Placement window is currently active
- For scheduled observations: observation was previously scheduled with date, lesson topic, and slot

### Screen flow

1. **Entry** — Practicum → Observations. Default view: "My Observations" (filtered to supervised trainees). Tabs/sections: Upcoming and Completed. Today's scheduled observations highlighted.

2. **Open observation** — Tap a scheduled observation. Detail shows: trainee name, host school, class/subject, scheduled date/time, lesson topic, prominent "Start Observation" button.
   - For unplanned observations: "+ Log Observation" button opens the same form pre-filled with current trainee/school context.

3. **Conduct observation (form)** — Sections:

   a) **Lesson context** (auto-filled from schedule, editable): date, time, subject, level/year (e.g., Tahun 4), topic of lesson, number of pupils present.

   b) **Rubric scoring** — Structured rubric with categories scored on a defined scale. **The rubric is BPG-standardized and locked at campus level** (BPG configures it; campuses cannot alter). Default categories likely include: Lesson Planning, Classroom Management, Pedagogical Skill, Student Engagement, Subject Mastery, Reflection.

   c) **Qualitative notes**: strengths observed, areas for improvement, specific incidents/examples.

   d) **Recommendations**: action items for the trainee before next observation.

   e) **Attachments**: photos of classroom, scanned lesson plan, copy of trainee's reflection — optional.

   f) **Status toggle**: "Save as Draft" vs "Submit Final".

4. **Submit** — On submit:
   - Observation locked (no further edits without re-open action)
   - Trainee notified that the observation report is available
   - Observation added to trainee's practicum history (visible in Trainee 360)
   - Rubric scores feed running practicum performance metric (used in W2.5)

### Inputs / outputs
- **Inputs**: rubric scores, qualitative notes, recommendations, optional attachments
- **Outputs**: finalized Observation record; notification to trainee; updated practicum history; data point for final evaluation (W2.5)

### Handoffs
- **To Trainee**: notification with observation summary and recommendations. Trainee can view but not edit.
- **To Penyelaras Praktikum**: aggregated into campus-wide progress dashboard (W3.5) — used to monitor whether observations are being conducted on schedule.
- **To Hat 2 self at evaluation time**: observation history is the primary input for the final practicum evaluation (W2.5).

### Success state
Observation appears in trainee's practicum record as a finalized entry, trainee has been notified, and supervisor's "Upcoming" list no longer shows this observation.

### Edge cases
- **Observation interrupted** (school emergency, lesson cancelled): mark as "Aborted" with reason, reschedule.
- **Pensyarah not on-site** (remote observation via video): "Remote" flag on the observation; rubric still applies but attachments may include video links.
- **Trainee disputes the report**: mechanism to log a comment or request a discussion — but the original report stays intact. No silent edits to a submitted report; corrections must be tracked amendments.
- **Pensyarah saved a draft and never returned**: drafts older than the practicum window surface as exceptions in the Penyelaras's monitoring view (W3.5).
- **Multiple Pensyarah observe the same lesson** (joint visit): allow multi-supervisor observations — each submits their own report, system shows them as linked.

---

## W2.4 — Review and comment on a trainee's logbook / reflection

**Module**: Practicum → trainee detail → Logbook tab
**Frequency**: Weekly per trainee throughout practicum

### Trigger
Trainee has submitted a logbook entry; Pensyarah reviews periodically.

### Pre-conditions
- Trainee has submitted at least one logbook entry
- Practicum window is active or recently completed

### Screen flow
1. **Entry** — Open trainee's Logbook tab. Chronological list (newest first): date, week number, summary, review status (`Unreviewed` / `Reviewed`).
2. **Open entry** — Trainee's reflection text, optional attachments (photos, lesson plans), comment area for Pensyarah.
3. **Comment and acknowledge** — Pensyarah writes feedback (encouragement, redirection, questions) and marks entry `Reviewed`. Trainee notified.
4. **Threaded discussion** — **Lightweight reply threads enabled in v1**. Trainee can reply to Pensyarah's comment; thread is per entry.

### Outputs
Reviewed logbook entries with comment threads.

### Handoffs
- Trainee: notification on each comment
- W2.5: cumulative logbook quality is one input to final evaluation

### Edge cases
- **Trainee never submits**: surface in W3.5 (Penyelaras dashboard) and as "attention needed" indicator on Pensyarah's My Trainees view
- **Trainee submits late or in bulk**: allow Pensyarah to review out of order; weeks tracked but not strictly sequential

---

## W2.5 — Submit the final practicum evaluation

**Module**: Practicum → trainee detail → Evaluation tab
**Frequency**: Once per trainee per practicum window

### Trigger
Practicum window is approaching its end (or has ended); Pensyarah submits the formal evaluation.

### Pre-conditions
- Practicum window is at or past evaluation phase (configurable threshold — e.g., evaluation tab unlocks at 80% of window elapsed)
- All scheduled observations are conducted (or the gap is acknowledged)

### Screen flow
1. **Entry** — Open Evaluation tab. Pre-populated context summary:
   - All conducted observations with rubric scores (auto-aggregated)
   - Logbook submission rate and review notes
   - Any disciplinary records during the window
   - **Trainee's self-evaluation** (in v1; submitted by trainee — workflow defined when we cover trainees)

2. **Final rubric** — Uses **BPG-standardized practicum rubric** (same one as W2.3, but a final cumulative version). Pre-populated with weighted averages from observation rubrics where applicable, but Pensyarah must explicitly score each category for the final.

3. **Narrative components** (required):
   - Overall summary of trainee's performance
   - Key strengths
   - Areas for continued development
   - Recommendation: **Pass / Pass with Distinction / Conditional Pass / Fail**
   - Conditions if Conditional Pass

4. **Attestation** — Pensyarah attests that evaluation reflects honest assessment based on observations and review of trainee's work.

5. **Submit** — On submit:
   - Evaluation locked
   - Trainee notified, can view (but not dispute through system in v1; disputes go through external channels)
   - Result feeds trainee's Transcript for that semester
   - Penyelaras sees evaluation as `Submitted` in W3.5

### Outputs
Finalized evaluation record. Result contributes to trainee's academic transcript.

### Handoffs
- Trainee: notification with full evaluation
- Transcripts module: result becomes part of academic record
- Penyelaras: visible in monitoring dashboard
- BPG: rolled up into ministry-level analytics

### Edge cases
- **Fail recommendation**: triggers parallel review process — IPG Admin and Penyelaras auto-notified. System should not let a `Fail` slip into the transcript silently.
- **Late submission** (past window-end): allowed but flagged in audit log
- **Trainee withdrew before evaluation**: skip — withdrawal supersedes evaluation
- **Pensyarah reassigned during window**: new supervisor submits evaluation, but form clearly shows observations conducted by both, each labelled

---

## W2.6 — Coordinate with host school

**Module**: Practicum → trainee detail; may overlap with School Coordination
**Frequency**: As-needed throughout practicum

### Trigger
Pensyarah needs to communicate with host school principal or administration about a specific trainee — observation scheduling, addressing school concerns, acknowledging school feedback.

### Pre-conditions
Trainee placement confirmed; Pensyarah is the assigned supervisor.

### Screen flow
1. **Entry** — From trainee's Overview tab, host school contact info + "Coordinate with School" action.
2. **Action options** (light-touch by design — host school's involvement is minimal per checklist §6.1):
   - **Send a coordination note** — short message attached to this trainee's placement; appears in host school's School-mode interface as notification on the trainee's record
   - **Log a school feedback** — record something the school raised verbally/by email, with date and notes
   - **View placement letter and history** — read-only
3. **Audit trail** — All notes logged on the placement record, visible to Penyelaras in W3.5 and forming part of the trainee's practicum history.

### Outputs
Coordination note records, viewable by Pensyarah, Penyelaras, and (lightweight notes only) the host school principal.

### Edge cases
- **School raises serious concern** (e.g., trainee misconduct at school): Pensyarah can **escalate** the coordination note, auto-notifying Penyelaras and IPG Admin and may trigger a Laporan Disiplin (W1.5)
- **Multiple Pensyarah coordinating with same school** (different trainees): school's view groups notes by trainee, not by Pensyarah
- **Note added after window closes**: allowed but flagged

---

# Hat 3: Penyelaras Praktikum Workflows

## W3.1 — Plan a practicum window

**Module**: Practicum → Placements landing area, or a dedicated "Windows" tab
**Frequency**: A handful of times per academic year — one per cohort phase

### Trigger
Cohort approaching scheduled practicum period; Penyelaras needs to formally open a practicum window before placements can be made.

### Pre-conditions
- Cohort exists and is in a semester where practicum is curriculum-mandated
- Academic calendar has space allocated for the practicum period

### Screen flow
1. **Entry** — Open Practicum → "+ New Practicum Window".
2. **Define window**:
   - Window name (e.g., "Praktikum PISMP Matematik Ambilan Jun 2023 — Sem 5")
   - Eligible cohort(s) — multi-select
   - Start and end dates
   - Subject scope (which subjects/levels trainees may teach; defaults from major)
   - Capacity per school (default cap on trainees per school; overridable per-school later)
   - Notes (free text)
3. **Activate** — Save creates window in `Draft` state. Penyelaras reviews eligible trainee count (auto-computed from cohort membership) and **activates**. Activation unlocks W3.2 (placements) and W3.3 (supervisor assignment) for this window.

### Outputs
Practicum Window record. Eligible trainees auto-derived from cohort membership.

### Handoffs
- Self (W3.2 / W3.3): window must be active to begin placements and assignments
- IPG Admin / BPG: window appears in monitoring views

### Edge cases
- **Trainee leaves cohort after window creation**: auto-drops from eligibility; existing placement flagged for review
- **Window dates overlap another active window for same cohort**: warn — usually a mistake, but allow override
- **Cancellation after activation**: only allowed before any confirmed placements; after that, W3.6 territory

---

## W3.2 — Assign trainees to host schools (placement)

**Module**: Practicum → Placements
**Frequency**: Once per practicum window per cohort

### Trigger
Practicum window planned (W3.1), cohort approaching practicum period; Penyelaras must place every eligible trainee at a host school.

### Pre-conditions
- Practicum window exists with start/end dates and eligible cohorts (W3.1)
- Trainees in eligible cohort(s) exist and are in good standing
- Pool of host schools available

### Cross-mode dependency
Host schools come from **School mode**, but Penyelaras selects from a **pre-curated "approved practicum schools" list maintained by IPG Admin**. This means IPG Admin needs an admin surface to maintain that list (selecting from School-mode schools). The Penyelaras does NOT pick from the entire School-mode school registry — only from the approved list.

### Screen flow
1. **Entry** — Practicum → Placements. Campus-wide view: active windows at top; each window expandable to show # eligible trainees, # placed, # unplaced, # schools used. Prominent "Start Placement" button on any window with unplaced trainees.

2. **Open a practicum window** — Placement workspace, two main panels:
   - **Left**: list of eligible trainees, filterable by cohort/major, status badges (`Unplaced` / `Placed` / `Confirmed`)
   - **Right**: list of approved host schools, filterable by location/type/capacity, with availability info (remaining trainee slots)

3. **Make a placement (single trainee)** — Two interaction patterns:
   - **Trainee-first**: select trainee, pick school from filtered list (filter respects trainee's major)
   - **School-first**: select school, see remaining capacity, drag/assign multiple trainees

   Both produce same outcome: a Placement record linking trainee + school + practicum window.

4. **Bulk placement (optional power feature)** — For large cohorts: import a placement plan (CSV) or use a "suggest placements" action. Defer suggestion intelligence to v2; CSV import alone is enough for v1.

5. **Capture placement details** — Per placement:
   - Trainee
   - Host school
   - Practicum window (auto from context)
   - Subject(s) the trainee will teach
   - Level(s)/year(s) (e.g., Tahun 3, Tahun 5)
   - Expected start/end dates within the window (default to full window)
   - Notes (optional)

6. **Confirm and dispatch** — Placements are now in `Placed` state but not yet `Confirmed`. Confirmation comes after host school principal acknowledges the placement letter (W3.4).

### Outputs
Placement records (one per trainee), status `Placed`. These records become visible to host school in School mode (trainee appears with "trainee" tag — per checklist §6.1) **only after confirmation** in W3.4.

### Handoffs
- Host School (School mode): trainee appears with "trainee" tag once placement is confirmed (post-W3.4). Pre-confirmation, NOT visible — schools should only see trainees they've formally accepted.
- Pensyarah (supervisors): not yet — supervisor assignment is W3.3, runs in parallel or after.
- Trainee: notification once placement is confirmed (post-W3.4) with school, subjects, dates.

### Success state
Every eligible trainee in the practicum window has a Placement record. Workspace shows zero unplaced trainees.

### Edge cases
- **No school available** for a trainee's major: trainee stays Unplaced with a flag; Penyelaras must escalate (manual intervention)
- **School over-capacity**: hard limit; allow override only with recorded reason
- **Trainee withdrawal mid-process**: allow placement removal; if confirmed, becomes part of W3.6
- **Late additions**: trainee becomes eligible after bulk placement (e.g., returning from medical leave) — Penyelaras places individually
- **Duplicate placements**: prevent same trainee being placed at two schools in same window

---

## W3.3 — Assign supervisors (Pensyarah) to trainees

**Module**: Practicum → Supervisors
**Frequency**: Once per practicum window, after or in parallel with W3.2

### Trigger
Placements made or in progress; Penyelaras assigns supervising Pensyarah to every placed trainee.

### Pre-conditions
- Placements exist for the practicum window
- Pool of Pensyarah available
- Pensyarah workload constraints known (e.g., max trainees per supervisor)

### Screen flow
1. **Entry** — Practicum → Supervisors. View shows: active windows; per window: # placements, # with assigned supervisors, # unassigned. Workload summary: each Pensyarah's current load (e.g., "Dr. Aminah — 8 trainees").

2. **Workspace** — Tap a window, two panels:
   - **Left**: placed trainees with school + major info, filterable
   - **Right**: available Pensyarah with current load, specialization (major), location info (some Pensyarah only supervise nearby schools)

3. **Assign** — Same dual interaction pattern as W3.2:
   - **Trainee-first**: pick trainee, pick supervisor (filtered by major-fit and proximity if data available)
   - **Supervisor-first**: pick Pensyarah, assign multiple trainees in one go

4. **Workload guardrails** — System enforces a configurable maximum trainees per Pensyarah (default 8). Exceeding requires override with recorded reason. Real-time load indicator.

5. **Confirm** — Assignments saved as records linking Placement → Pensyarah. Pensyarah's "My Trainees" view (Hat 2 W2.1 entry point) now shows new assignments.

### Major-mismatch handling
**Allow with warning** — major-fit is preferred but not always achievable; show a clear warning when assigning a Pensyarah whose major differs from trainee's, but don't block.

### Outputs
Supervisor assignment records, attached to placements.

### Handoffs
- Pensyarah (Hat 2): notification — "You've been assigned X trainees for [practicum window]." Triggers Hat 2 workflows (W2.1 → W2.6).
- Trainee: notification once supervisor finalized — they should know who their Pensyarah Penyelia is.
- W3.5: feeds campus-wide progress dashboard.

### Success state
Every placed trainee has an assigned supervisor. No "Unassigned" entries remain.

### Edge cases
- **Mid-window reassignment** (Pensyarah falls ill, takes leave): reassignment allowed; observation history travels with trainee, not original supervisor; new supervisor inherits visibility into past observations. Part of W3.6.
- **Imbalanced load**: provide "rebalance" suggestion — v2.
- **Major mismatch**: warn (don't block); IPGs sometimes cross-assign due to staffing.
- **Pensyarah on leave during practicum window**: prevent assignment; surface as constraint in workspace.

---

## W3.4 — Generate and dispatch placement letters

**Module**: Practicum → School Coordination
**Frequency**: After W3.2, batched per practicum window

### Trigger
Placements made (W3.2 complete or near-complete); Penyelaras must formally notify each host school and obtain principal acknowledgement before placements move from `Placed` → `Confirmed`.

### Pre-conditions
- Placements exist in `Placed` state
- A placement letter template exists — **BPG-configured, locked at campus level** (consistent with rubric pattern)

### Screen flow
1. **Entry** — Practicum → School Coordination. List of practicum windows with dispatch status (e.g., "12 of 15 schools acknowledged").

2. **Generate letters** — Choose a window, click "Generate Letters". System produces one letter per host school (not per trainee — letter lists all trainees being placed at that school). Letters generated from the BPG template with auto-filled fields (school name, principal name, trainee list, dates, supervising Pensyarah list, IPG contact).

3. **Review and send** — Penyelaras previews each letter, edits if needed, and dispatches. Dispatch produces:
   - Document record attached to host school's School-mode Documents area (per checklist §6.1 — placement letter is the cross-mode artifact)
   - Notification to host school principal in School mode
   - Placement record's status moves to `Pending Acknowledgement`

4. **Track acknowledgements** — Dispatch dashboard shows per-school status:
   - `Sent` (dispatched, awaiting acknowledgement)
   - `Acknowledged` (principal confirmed)
   - `Declined` (principal rejected, with reason)
   - `No Response` (after configurable threshold, e.g., 7 days)

5. **Confirmation** — When a school acknowledges, all placements at that school move from `Pending Acknowledgement` → `Confirmed`. **This is the trigger for**:
   - Trainee tag becoming visible in host school's School-mode interface
   - Trainee receiving notification with final placement details
   - Supervising Pensyarah formally notified to begin Hat 2 workflows

### Handoffs
- Host School Principal (School mode): receives placement letter as a document + notification with acknowledge/decline action
- Trainee: notification on confirmation
- Supervisor: notification on confirmation

### Edge cases
- **School declines**: placement returns to `Unplaced`; Penyelaras must reassign (back to W3.2 for that trainee)
- **No response past threshold**: surface as exception in W3.5; Penyelaras may resend, escalate, or reassign
- **Letter content needs amendment** post-dispatch: regeneration creates a versioned letter; old version remains in audit trail

---

## W3.5 — Monitor campus-wide practicum progress

**Module**: Practicum → "Dashboard" or default landing view
**Frequency**: Ongoing throughout active practicum windows

### Trigger
Practicum window is active; Penyelaras checks status periodically (daily/weekly) to spot issues before they become problems.

### Pre-conditions
At least one active practicum window with confirmed placements.

### Screen flow
1. **Entry** — Penyelaras lands on Practicum, sees campus-wide dashboard for active window(s).

2. **Dashboard panels**:
   - **Placement health**: total placed / confirmed / declined / unplaced
   - **Observation progress**: scheduled vs completed per trainee, with red flags for trainees with zero observations past expected milestone
   - **Logbook compliance**: which trainees are submitting weekly reflections, which are behind
   - **Evaluation status**: how many final evaluations submitted as window closes
   - **Exceptions queue**: anything needing intervention — declined schools, no-response schools, supervisor reassignment requests, withdrawn trainees, draft observations stuck in limbo

3. **Drill in** — Each panel clickable into a filtered list (e.g., tap "5 trainees with no observations" → list).

4. **Take action** — From any drilled-in view:
   - Nudge a Pensyarah ("Your trainee X has had no observations yet")
   - Reassign a supervisor (W3.6)
   - Reassign a placement (W3.6)
   - Mark an exception as resolved with notes

### Outputs
Notifications, exception resolutions, audit log entries.

### Handoffs
- Pensyarah: nudges land in their notifications
- IPG Admin: critical exceptions (e.g., 3+ unplaced trainees with window starting in 5 days) auto-escalate
- BPG: rolled-up campus metrics feed BPG ministry view

### Edge cases
- **Stale data**: define refresh expectations clearly (real-time on action, periodic recompute for metrics)
- **Stuck draft observation older than 7 days**: surface prominently — supervisor started but never completed an observation

---

## W3.6 — Handle exceptions

**Module**: Practicum → contextual from W3.5 dashboard, or dedicated Exceptions area
**Frequency**: As-needed throughout practicum windows

### Trigger
Something goes wrong: trainee withdraws, supervisor falls ill, school cancels mid-window, etc. Surfaces in W3.5 or reported manually (by trainee, supervisor, or school).

### Pre-conditions
A practicum window is active and an exception condition has occurred.

### Common exception flows

**Trainee withdrawal**
- Penyelaras opens trainee's placement, marks "Withdraw Trainee" with reason (medical, disciplinary, personal, etc.)
- Placement status → `Withdrawn`
- Host school notified (trainee tag removed)
- Supervisor notified (assigned trainee count decreases)
- Trainee notified
- All observations/logbook entries preserved but archived

**Supervisor reassignment**
- Penyelaras opens assignment, marks "Reassign Supervisor" with reason
- Selects new Pensyarah (with same workload guardrails as W3.3)
- Old supervisor's observation history transfers to new supervisor's view (read-only access)
- Both Pensyarah notified; trainee notified

**Host school cancellation mid-window**
- Penyelaras opens school's placement group, marks "School Cancelled" with reason
- All trainees at that school enter `Pending Re-placement` state
- Penyelaras must run a mini-W3.2 to re-place each trainee
- All affected parties notified

**Late additions**
- Trainee becomes newly eligible (returning from leave)
- Penyelaras places them via W3.2 individually, assigns supervisor via W3.3 individually, dispatches single-letter via W3.4

### Outputs
Updated placement/assignment records with audit trail (who changed what, when, why).

### Handoffs
Notifications to all affected actors. Exception resolution logged for BPG-level reporting.

### Edge cases
- **Cascading exceptions** (trainee withdrawal + supervisor was already overloaded): system shouldn't require Penyelaras to re-balance the supervisor's load reactively unless they choose to
- **Exception during evaluation phase** (W2.5 in progress): partial evaluations preserved; if supervisor reassigned during evaluation, new supervisor sees partial work but must finalize themselves
- **Audit completeness**: every exception action must be logged with who/what/when/why — matters for BPG oversight

---

## Implementation surfaces implied by Pensyarah workflows

These are admin/configuration surfaces hinted at by various workflows. Implementing Pensyarah workflows requires the following also exist:

1. **BPG admin surface** — manage observation rubric (W2.3, W2.5), placement letter template (W3.4)
2. **IPG Admin surface** — maintain approved practicum schools list (W3.2), configure Pensyarah workload max (W3.3), configure leave-response timeout (W1.4), configure attendance late-edit threshold (W1.1), configure assignment late-penalty defaults (W1.7.2)
3. **Course grading scheme setup** — defined per course/cohort, drives W1.2 gradebook columns and W1.7 coursework weighting
4. **Cohort grading scheme setup** — defined per course/cohort, used by W1.2 / W1.7
5. **Trainee enrollment in courses** — pre-condition for W1.1, W1.2, W1.7
6. **Trainee enrollment in co-curriculum units** — pre-condition for W1.9
7. **Research project assignment** (likely owned by Ketua Jabatan) — pre-condition for W1.8
8. **Co-curriculum advisor assignment** (likely owned by IPG Admin or Ketua Jabatan) — pre-condition for W1.9

These should be captured when we map workflows for IPG Admin, BPG, and Ketua Jabatan.

---

## Open items deferred to other actors' workflow design

These are referenced but not defined in this document; they belong to other actors' workflows:

- **Trainee submits logbook entry** — referenced by W2.4
- **Trainee submits self-evaluation** — referenced by W2.5
- **Trainee takes online test** — referenced by W1.7.5
- **Trainee submits assignment** — referenced by W1.7.2 / W1.7.6
- **Host school principal acknowledges placement letter** — referenced by W3.4 (this is a School-mode workflow)
- **IPG Admin processes leave request and disciplinary report** — referenced by W1.4 / W1.5
- **Ketua Jabatan assigns research supervisor** — referenced by W1.8

---

*End of Pensyarah workflows. Next actors to document: Guru Pelatih (Trainee), Ketua Jabatan, IPG Admin, BPG.*
