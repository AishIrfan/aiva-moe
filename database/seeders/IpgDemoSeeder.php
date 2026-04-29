<?php

namespace Database\Seeders;

use App\Models\ApprovedPracticumSchool;
use App\Models\Assessment;
use App\Models\Campus;
use App\Models\CocurricularActivity;
use App\Models\CocurricularParticipation;
use App\Models\Cohort;
use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\CourseMaterialCategory;
use App\Models\CourseMaterialFile;
use App\Models\CourseOffering;
use App\Models\DisciplineCategory;
use App\Models\DisciplineIncident;
use App\Models\GradebookColumn;
use App\Models\IpgAttendanceRecord;
use App\Models\IpgAttendanceSession;
use App\Models\IpgDisciplineCase;
use App\Models\IpgDisciplineCaseEvidence;
use App\Models\IpgDisciplineCaseWitness;
use App\Models\IpgLeaveRequest;
use App\Models\IpgLeaveRequestPensyarahResponse;
use App\Models\OnlineTestQuestion;
use App\Models\OnlineTestQuestionOption;
use App\Models\User;
use App\Models\ObservationRubric;
use App\Models\ObservationRubricCategory;
use App\Models\Pensyarah;
use App\Models\Placement;
use App\Models\PlacementLetterTemplate;
use App\Models\PracticumWindow;
use App\Models\ResearchProject;
use App\Models\School;
use App\Models\Semester;
use App\Models\TimetableSession;
use App\Models\Trainee;
use App\Models\TranscriptEntry;
use Illuminate\Database\Seeder;

/**
 * Seed demo data for the new IPG modules introduced in Phases 4–7:
 *  - Academics: Courses, Transcripts, Co-curriculum, Research (Phase 4)
 *  - Practicum: Placements, Observations, Evaluations, Logbook, Letters (Phase 5)
 *  - Hostel: Blocks, Rooms, Assignments (Phase 7)
 *
 * Idempotent — uses firstOrCreate / updateOrCreate so it can re-run safely.
 */
class IpgDemoSeeder extends Seeder
{
    public function run(): void
    {
        $campus = Campus::first();
        if (! $campus) return;

        $semester = $campus->currentSemester();
        if (! $semester) return;

        // Wave 1 — foundation tables (must run before practicum/academic seeds
        // since placements/offerings depend on them).
        $this->seedObservationRubric($campus);
        $this->seedPlacementLetterTemplate($campus);
        $this->seedApprovedPracticumSchools($campus);
        $window = $this->seedPracticumWindow($campus);
        $this->seedCourseOfferings($campus, $semester);
        $this->seedTimetableSessions($campus, $semester);

        // Wave 2 Unit A — course materials (depends on offerings).
        $this->seedCourseMaterialCategories();
        $this->seedCourseMaterials($campus, $semester);

        // Wave 2 Unit C — per-session attendance (depends on offerings + timetable + trainees).
        $this->seedIpgAttendance($campus, $semester);

        // Wave 2 Unit B — assessments + gradebook columns + online test question bank.
        $this->seedAssessments($campus, $semester);

        // Wave 2 Unit D — IPG leave / MC requests + per-pensyarah course-impact responses.
        $this->seedIpgLeaveRequests($campus, $semester);

        // Wave 2 Unit E — discipline categories + incidents + cases + evidence + witnesses.
        $this->seedDisciplineCategories();
        $this->seedIpgDisciplineCases($campus, $semester);

        // Existing demo waves (4–7) — practicum now references the window.
        $this->seedAcademics($campus, $semester);
        $this->seedPracticum($campus, $semester, $window);
        $this->seedHostel($campus, $semester);
    }

    // ============ Wave 1: Foundations ============

    /**
     * Seeds a default 6-category BPG observation rubric and pins it on the campus.
     * Categories taken from IPG_WORKFLOWS.md §W2.3 default list.
     */
    protected function seedObservationRubric(Campus $campus): void
    {
        $rubric = ObservationRubric::updateOrCreate(
            ['name' => 'BPG Praktikum Observation Rubric', 'version' => 'v2025.1'],
            [
                'status'       => ObservationRubric::STATUS_ACTIVE,
                'applied_from' => '2025-01-01',
                'description'  => 'Standard BPG-issued rubric for practicum classroom observations. Six categories scored 1–4.',
            ]
        );

        $categories = [
            ['Lesson Planning',     'Quality of lesson plan, alignment with curriculum, preparedness.'],
            ['Classroom Management', 'Discipline, transitions, time management, pupil behaviour.'],
            ['Pedagogical Skill',   'Teaching strategies, differentiation, use of resources.'],
            ['Student Engagement',  'Participation, motivation, inclusivity, formative checks.'],
            ['Subject Mastery',     'Content accuracy, depth, response to pupil questions.'],
            ['Reflection',          'Post-lesson awareness of strengths and areas for growth.'],
        ];

        foreach ($categories as $idx => [$label, $description]) {
            ObservationRubricCategory::updateOrCreate(
                ['observation_rubric_id' => $rubric->id, 'label' => $label],
                ['max_score' => 4, 'display_order' => $idx, 'description' => $description]
            );
        }

        if ($campus->current_observation_rubric_id !== $rubric->id) {
            $campus->update(['current_observation_rubric_id' => $rubric->id]);
        }
    }

    /**
     * Seeds a default BPG placement letter template and pins it on the campus.
     */
    protected function seedPlacementLetterTemplate(Campus $campus): void
    {
        $template = PlacementLetterTemplate::updateOrCreate(
            ['name' => 'BPG Standard Placement Letter', 'version' => 'v2025.1'],
            [
                'status'                 => PlacementLetterTemplate::STATUS_ACTIVE,
                'applied_from'           => '2025-01-01',
                'subject_line'           => 'Penempatan Guru Pelatih untuk Praktikum',
                'body'                   => "Tuan/Puan Pengetua,\n\nSaya, bagi pihak {campus_name}, ingin memaklumkan bahawa Guru Pelatih kami di bawah akan menjalani Praktikum di {host_school_name} dari {start_date} sehingga {end_date}.\n\nSenarai Guru Pelatih: {trainee_list}\nPensyarah Penyelia: {supervisor_list}\n\nSila hubungi {ipg_contact} untuk sebarang pertanyaan.\n\nSekian, terima kasih.",
                'available_placeholders' => [
                    'campus_name', 'host_school_name', 'principal_name',
                    'start_date', 'end_date',
                    'trainee_list', 'supervisor_list', 'ipg_contact',
                ],
            ]
        );

        if ($campus->current_placement_letter_template_id !== $template->id) {
            $campus->update(['current_placement_letter_template_id' => $template->id]);
        }
    }

    /**
     * Approves the demo school as a practicum host for the campus.
     */
    protected function seedApprovedPracticumSchools(Campus $campus): void
    {
        foreach (School::all() as $school) {
            ApprovedPracticumSchool::updateOrCreate(
                ['campus_id' => $campus->id, 'school_id' => $school->id],
                ['default_capacity' => 6, 'notes' => 'Approved for demo seed.']
            );
        }
    }

    /**
     * Seeds a single active practicum window covering all campus cohorts.
     * Returns the window so downstream practicum seed can backfill it.
     */
    protected function seedPracticumWindow(Campus $campus): PracticumWindow
    {
        $now = now();

        $window = PracticumWindow::updateOrCreate(
            ['campus_id' => $campus->id, 'name' => 'Praktikum PISMP — Fasa 2 (Demo)'],
            [
                'start_date'                  => $now->copy()->subWeeks(3)->toDateString(),
                'end_date'                    => $now->copy()->addWeeks(9)->toDateString(),
                'status'                      => PracticumWindow::STATUS_ACTIVE,
                'default_capacity_per_school' => 4,
                'notes'                       => 'Auto-seeded demo window covering all campus cohorts.',
            ]
        );

        // Sync eligible cohorts (idempotent).
        $cohortIds = Cohort::where('campus_id', $campus->id)->pluck('id')->all();
        $window->cohorts()->sync($cohortIds);

        return $window;
    }

    /**
     * Creates one CourseOffering per (Course × Cohort × current Semester).
     * Lecturer is rotated across pensyarahs so each one has at least one course.
     */
    protected function seedCourseOfferings(Campus $campus, Semester $semester): void
    {
        $courses = Course::where('campus_id', $campus->id)->get();
        $cohorts = Cohort::where('campus_id', $campus->id)->get();
        $pensyarahs = Pensyarah::where('campus_id', $campus->id)->orderBy('id')->get();
        if ($pensyarahs->isEmpty()) return;

        $i = 0;
        foreach ($courses as $course) {
            foreach ($cohorts as $cohort) {
                $lecturer = $pensyarahs[$i++ % $pensyarahs->count()];
                CourseOffering::updateOrCreate(
                    [
                        'course_id'   => $course->id,
                        'cohort_id'   => $cohort->id,
                        'semester_id' => $semester->id,
                    ],
                    [
                        'lecturer_pensyarah_id' => $lecturer->id,
                        'status'                => CourseOffering::STATUS_ACTIVE,
                    ]
                );
            }
        }
    }

    /**
     * Seeds a small starter timetable: each course offering gets one weekly slot.
     * Days/periods rotate so the lecturer's weekly view has variety.
     */
    protected function seedTimetableSessions(Campus $campus, Semester $semester): void
    {
        $offerings = CourseOffering::query()
            ->where('semester_id', $semester->id)
            ->whereHas('cohort', fn ($q) => $q->where('campus_id', $campus->id))
            ->get();

        $rooms = ['A-201', 'A-202', 'A-301', 'B-105', 'B-204', 'C-110'];
        $slots = [
            // [day_of_week, period_label, start, end]
            [1, 'P1', '08:00', '09:30'],
            [2, 'P2', '09:45', '11:15'],
            [3, 'P3', '11:30', '13:00'],
            [4, 'P4', '14:00', '15:30'],
            [5, 'P5', '15:45', '17:15'],
        ];

        foreach ($offerings as $idx => $offering) {
            [$day, $period, $start, $end] = $slots[$idx % count($slots)];
            TimetableSession::updateOrCreate(
                [
                    'course_offering_id' => $offering->id,
                    'day_of_week'        => $day,
                    'start_time'         => $start,
                ],
                [
                    'period_label' => $period,
                    'end_time'     => $end,
                    'room'         => $rooms[$idx % count($rooms)],
                ]
            );
        }
    }

    // ============ Wave 2 Unit A: Course Materials ============

    /**
     * Seeds the 6 default course-material categories from W1.7.1.
     * Global lookup table (not campus-scoped in v1).
     */
    protected function seedCourseMaterialCategories(): void
    {
        $defaults = [
            ['slug' => 'course-notes',     'name' => 'Course Notes'],
            ['slug' => 'slides',           'name' => 'Slides'],
            ['slug' => 'past-year-exams',  'name' => 'Past Year Exam Papers'],
            ['slug' => 'references',       'name' => 'References'],
            ['slug' => 'worksheets',       'name' => 'Worksheets'],
            ['slug' => 'other',            'name' => 'Other'],
        ];

        foreach ($defaults as $idx => $cat) {
            CourseMaterialCategory::updateOrCreate(
                ['slug' => $cat['slug']],
                ['name' => $cat['name'], 'sort_order' => $idx, 'is_active' => true]
            );
        }
    }

    /**
     * Seeds three demo materials per course offering — two visible (Slides
     * Week 1, Course Notes Week 2) and one hidden draft (References, prep
     * ahead). Each material has one stub file row pointing at a placeholder
     * path on the `local` disk. We deliberately do NOT write binary files in
     * the seeder; downloads will 404 in demo until the upload UI lands.
     */
    protected function seedCourseMaterials(Campus $campus, Semester $semester): void
    {
        $offerings = CourseOffering::query()
            ->where('semester_id', $semester->id)
            ->whereHas('cohort', fn ($q) => $q->where('campus_id', $campus->id))
            ->with('lecturer.user')
            ->get();

        $slides     = CourseMaterialCategory::where('slug', 'slides')->first();
        $notes      = CourseMaterialCategory::where('slug', 'course-notes')->first();
        $references = CourseMaterialCategory::where('slug', 'references')->first();
        if (! $slides || ! $notes || ! $references) return;

        $template = [
            ['title' => 'Slides — Minggu 1: Pengenalan',     'category' => $slides,     'week' => 1, 'visibility' => CourseMaterial::VISIBILITY_VISIBLE,      'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'ext' => 'pptx', 'size' => 1_842_000],
            ['title' => 'Nota Kuliah — Minggu 2',             'category' => $notes,      'week' => 2, 'visibility' => CourseMaterial::VISIBILITY_VISIBLE,      'mime' => 'application/pdf',                                                            'ext' => 'pdf',  'size' => 612_000],
            ['title' => 'Bahan Rujukan Tambahan',             'category' => $references, 'week' => null, 'visibility' => CourseMaterial::VISIBILITY_HIDDEN_DRAFT, 'mime' => 'application/pdf',                                                            'ext' => 'pdf',  'size' => 980_000],
        ];

        foreach ($offerings as $offering) {
            $uploaderUserId = $offering->lecturer?->user?->id;

            foreach ($template as $idx => $row) {
                $material = CourseMaterial::updateOrCreate(
                    ['course_offering_id' => $offering->id, 'title' => $row['title']],
                    [
                        'course_material_category_id' => $row['category']->id,
                        'description'                 => null,
                        'week_number'                 => $row['week'],
                        'visibility'                  => $row['visibility'],
                        'sort_order'                  => $idx,
                        'created_by_user_id'          => $uploaderUserId,
                        'updated_by_user_id'          => $uploaderUserId,
                    ]
                );

                $filename = sprintf('offering-%d-material-%d.%s', $offering->id, $idx + 1, $row['ext']);
                CourseMaterialFile::updateOrCreate(
                    ['course_material_id' => $material->id, 'original_filename' => $filename],
                    [
                        'disk'                 => config('ipg.uploads.course_materials.disk', 'local'),
                        'path'                 => 'course-materials/demo/'.$filename,
                        'mime_type'            => $row['mime'],
                        'size_bytes'           => $row['size'],
                        'sort_order'           => 0,
                        'replaced_at'          => null,
                        'uploaded_by_user_id'  => $uploaderUserId,
                    ]
                );
            }
        }
    }

    // ============ Wave 2 Unit C: IPG Attendance ============

    /**
     * Seeds 4 past attendance sessions per course offering, anchored to the
     * semester's start_date (idempotent — keys are stable across re-runs):
     *
     *   wk+4: normal (timetable-linked), recorded, LOCKED (past edit threshold)
     *   wk+5: normal, recorded, still editable
     *   wk+6: ad-hoc (no timetable link), recorded by a substitute pensyarah
     *   wk+7: cancelled (no records, carries cancellation_reason)
     *
     * Per-record status distribution is realistic, not uniform-present:
     *   ~85% present, ~7% late (5–25 min), ~5% absent, ~2% excused_mc, ~1% excused_leave.
     * Driven by a deterministic hash of (trainee_id, session_id) so re-runs
     * produce stable values.
     */
    protected function seedIpgAttendance(Campus $campus, Semester $semester): void
    {
        $offerings = CourseOffering::query()
            ->where('semester_id', $semester->id)
            ->whereHas('cohort', fn ($q) => $q->where('campus_id', $campus->id))
            ->with(['cohort.trainees', 'timetableSessions', 'lecturer'])
            ->get();

        $pensyarahs = Pensyarah::where('campus_id', $campus->id)->orderBy('id')->get();
        if ($pensyarahs->isEmpty()) return;

        $semesterStart = $semester->start_date;
        $thresholdDays = (int) config('ipg.attendance.late_edit_threshold_days', 3);

        foreach ($offerings as $oIdx => $offering) {
            $timetable    = $offering->timetableSessions->first(); // 1 per offering per Wave 1 seed
            $primaryLecturerId = $offering->lecturer_pensyarah_id;
            // Substitute = next pensyarah by id, deterministic.
            $substitute = $pensyarahs->first(
                fn (Pensyarah $p) => $p->id !== $primaryLecturerId
            ) ?? $pensyarahs->first();

            $defaultStart = $timetable?->start_time ?? '08:00:00';
            $defaultEnd   = $timetable?->end_time   ?? '09:30:00';

            $sessions = [
                // [week_offset, timetable_link?, status, start, end, recorded_by, set_locked]
                [4, $timetable, IpgAttendanceSession::STATUS_RECORDED,  $defaultStart, $defaultEnd, $primaryLecturerId, true],
                [5, $timetable, IpgAttendanceSession::STATUS_RECORDED,  $defaultStart, $defaultEnd, $primaryLecturerId, false],
                [6, null,       IpgAttendanceSession::STATUS_RECORDED,  '14:00:00',    '15:30:00',  $substitute->id,    false],
                [7, $timetable, IpgAttendanceSession::STATUS_CANCELLED, $defaultStart, $defaultEnd, $primaryLecturerId, false],
            ];

            foreach ($sessions as $sIdx => [$weekOffset, $tt, $status, $start, $end, $recordedById, $setLocked]) {
                $sessionDate = $semesterStart->copy()->addWeeks($weekOffset)->toDateString();
                $isCancelled = $status === IpgAttendanceSession::STATUS_CANCELLED;

                $session = IpgAttendanceSession::updateOrCreate(
                    [
                        'course_offering_id' => $offering->id,
                        'session_date'       => $sessionDate,
                        'start_time'         => $start,
                    ],
                    [
                        'timetable_session_id'     => $tt?->id,
                        'end_time'                 => $end,
                        'status'                   => $status,
                        'cancellation_reason'      => $isCancelled ? 'Cuti umum — tiada kelas.' : null,
                        'recorded_by_pensyarah_id' => $isCancelled ? null : $recordedById,
                        'recorded_at'              => $isCancelled ? null : $semesterStart->copy()->addWeeks($weekOffset)->setTimeFromTimeString($end)->addMinutes(30),
                        'locked_at'                => $setLocked
                            ? $semesterStart->copy()->addWeeks($weekOffset)->setTimeFromTimeString($end)->addDays($thresholdDays + 1)
                            : null,
                        'notes'                    => null,
                    ]
                );

                if ($isCancelled) continue;

                foreach ($offering->cohort?->trainees ?? [] as $trainee) {
                    // Bucket bounds widened slightly from a strict 85/7/5/2/1
                    // to 85/7/4/2/2 so every status appears in 180-record demo
                    // (the deterministic hash skipped the lone 99-slot for
                    // excused_leave, leaving a UI-coverage gap).
                    $bucket = ($trainee->id * 13 + $session->id * 7) % 100;
                    if      ($bucket < 85) { $recStatus = IpgAttendanceRecord::STATUS_PRESENT;       $minsLate = null; }
                    elseif  ($bucket < 92) { $recStatus = IpgAttendanceRecord::STATUS_LATE;          $minsLate = 5 + (($trainee->id + $session->id) % 21); }
                    elseif  ($bucket < 96) { $recStatus = IpgAttendanceRecord::STATUS_ABSENT;        $minsLate = null; }
                    elseif  ($bucket < 98) { $recStatus = IpgAttendanceRecord::STATUS_EXCUSED_MC;    $minsLate = null; }
                    else                   { $recStatus = IpgAttendanceRecord::STATUS_EXCUSED_LEAVE; $minsLate = null; }

                    IpgAttendanceRecord::updateOrCreate(
                        [
                            'ipg_attendance_session_id' => $session->id,
                            'trainee_id'                => $trainee->id,
                        ],
                        [
                            'status'       => $recStatus,
                            'minutes_late' => $minsLate,
                            'notes'        => null,
                        ]
                    );
                }
            }
        }
    }

    // ============ Wave 2 Unit B: Assessments + Gradebook Columns + Online Test Question Bank ============

    /**
     * Seeds 1 of each Assessment kind (assignment / tutorial / f2f_test /
     * online_test) per course offering = 60 assessments. Plus 1 GradebookColumn
     * per offering (kind rotated manual/participation/bonus) = 15. Total 75
     * gradebook-feeding entries.
     *
     * State distribution targets ~75% published, ~17% draft, ~8% archived via
     * a deterministic bucket function. Group submissions enabled on every 3rd
     * offering's assignment. Late-penalty rules populated only on assignments.
     *
     * Online test questions: populated for offerings 1-8 only (8 of 15 tests).
     * Offerings 9-15 are intentional "shell" tests with no questions yet —
     * exercises the empty-state in W1.7.5 review/preview UI. Total 45
     * questions, mix ~64/36 MCQ/SA. Each MCQ has 4 options with one correct
     * (most) or two correct (multi-correct demo, every 13th question by id).
     */
    protected function seedAssessments(Campus $campus, Semester $semester): void
    {
        $offerings = CourseOffering::query()
            ->where('semester_id', $semester->id)
            ->whereHas('cohort', fn ($q) => $q->where('campus_id', $campus->id))
            ->orderBy('id')
            ->with('lecturer.user')
            ->get();

        $semesterStart = $semester->start_date;

        $kindOrder = [
            Assessment::KIND_ASSIGNMENT,
            Assessment::KIND_TUTORIAL,
            Assessment::KIND_F2F_TEST,
            Assessment::KIND_ONLINE_TEST,
        ];

        $gbKindRotation = [
            GradebookColumn::KIND_MANUAL,
            GradebookColumn::KIND_PARTICIPATION,
            GradebookColumn::KIND_BONUS,
        ];

        // Deterministic ~75/17/8 split via a coprime stride hash.
        $statusFor = static function (int $idx): string {
            $bucket = ($idx * 7 + 3) % 100;
            return $bucket < 75 ? Assessment::STATUS_PUBLISHED
                 : ($bucket < 92 ? Assessment::STATUS_DRAFT : Assessment::STATUS_ARCHIVED);
        };

        $oCount = 0;
        foreach ($offerings as $offering) {
            $oCount++;
            $userId = $offering->lecturer?->user?->id;

            foreach ($kindOrder as $kIdx => $kind) {
                $idx     = ($oCount - 1) * 5 + $kIdx;
                $status  = $statusFor($idx);
                $perKind = $this->assessmentPerKindAttrs($kind, $offering, $semesterStart);

                $assessment = Assessment::updateOrCreate(
                    [
                        'course_offering_id' => $offering->id,
                        'kind'               => $kind,
                        'title'              => $perKind['title'],
                    ],
                    array_merge($perKind, [
                        'status'             => $status,
                        'description'        => null,
                        'created_by_user_id' => $userId,
                        'updated_by_user_id' => $userId,
                    ])
                );

                // Question bank only for online tests on offerings 1-8.
                if ($kind === Assessment::KIND_ONLINE_TEST && $offering->id <= 8) {
                    $this->seedOnlineTestQuestions($assessment, $offering->id, $userId);
                }
            }

            // Gradebook column — 1 per offering, kind rotates.
            $gbKind   = $gbKindRotation[($offering->id - 1) % 3];
            $gbStatus = $statusFor(($oCount - 1) * 5 + 4);
            $gbTitle  = match ($gbKind) {
                GradebookColumn::KIND_MANUAL        => 'Markah Pembentangan (Manual)',
                GradebookColumn::KIND_PARTICIPATION => 'Penyertaan Kelas',
                GradebookColumn::KIND_BONUS         => 'Markah Bonus',
            };

            GradebookColumn::updateOrCreate(
                ['course_offering_id' => $offering->id, 'title' => $gbTitle],
                [
                    'kind'               => $gbKind,
                    'description'        => null,
                    'total_marks'        => 20.00,
                    'weight_pct'         => 5.00,
                    'status'             => $gbStatus,
                    'created_by_user_id' => $userId,
                    'updated_by_user_id' => $userId,
                ]
            );
        }
    }

    /**
     * Returns the kind-specific attribute bag for an assessment row.
     * Keeps the main loop in seedAssessments() readable.
     */
    protected function assessmentPerKindAttrs(string $kind, CourseOffering $offering, $semesterStart): array
    {
        return match ($kind) {
            Assessment::KIND_ASSIGNMENT => [
                'title'              => 'Tugasan 1: Rancangan Pengajaran Individu',
                'instructions'       => 'Hasilkan satu rancangan pengajaran lengkap untuk satu sesi (60 minit) berdasarkan topik yang dibincangkan dalam kuliah.',
                'total_marks'        => 100.00,
                'weight_pct'         => 15.00,
                'open_at'            => $semesterStart->copy()->addWeeks(4),
                'due_at'             => $semesterStart->copy()->addWeeks(6)->setTime(23, 59),
                'late_policy'        => Assessment::LATE_POLICY_ALLOWED_WITH_PENALTY,
                'late_penalty_rules' => [
                    'grace_hours'      => 24,
                    'per_day_pct'      => 10,
                    'max_pct'          => 50,
                    'after_max_action' => 'zero',
                ],
                'venue'              => null,
                'allowed_materials'  => null,
                'duration_minutes'   => null,
                'attempts_allowed'   => null,
                'result_release'     => null,
                'settings'           => [
                    'submission_types' => ['file'],
                    'file_constraints' => [
                        'max_files'     => 1,
                        'max_size_mb'   => 25,
                        'allowed_mimes' => ['application/pdf'],
                    ],
                    // Group submission enabled on every 3rd offering's assignment.
                    'group' => $offering->id % 3 === 0 ? [
                        'enabled'                     => true,
                        'min_size'                    => 2,
                        'max_size'                    => 4,
                        'formation'                   => 'self_formed',
                        'per_member_grade_adjustment' => true,
                    ] : ['enabled' => false],
                ],
            ],

            Assessment::KIND_TUTORIAL => [
                'title'              => 'Tutorial Minggu 3',
                'instructions'       => 'Jawab soalan latihan yang dibekalkan dan hantar dalam bentuk teks.',
                'total_marks'        => 10.00,
                'weight_pct'         => 2.00,
                'open_at'            => $semesterStart->copy()->addWeeks(2),
                'due_at'             => $semesterStart->copy()->addWeeks(3)->setTime(23, 59),
                'late_policy'        => Assessment::LATE_POLICY_ALLOWED_NO_PENALTY,
                'late_penalty_rules' => null,
                'venue'              => null,
                'allowed_materials'  => null,
                'duration_minutes'   => null,
                'attempts_allowed'   => null,
                'result_release'     => null,
                'settings'           => ['submission_types' => ['text']],
            ],

            Assessment::KIND_F2F_TEST => [
                'title'              => 'Ujian Pertengahan Semester',
                'instructions'       => 'Bawa pen, kalkulator saintifik dan kad pelajar. Sila tiba 10 minit sebelum waktu mula.',
                'total_marks'        => 50.00,
                'weight_pct'         => 25.00,
                'open_at'            => null,
                'due_at'             => $semesterStart->copy()->addWeeks(8)->setTime(9, 0),
                'late_policy'        => Assessment::LATE_POLICY_NOT_ALLOWED,
                'late_penalty_rules' => null,
                'venue'              => 'Dewan Kuliah ' . chr(65 + ($offering->id % 6)),
                'allowed_materials'  => 'Buku terbuka; kalkulator saintifik dibenarkan; tiada peranti komunikasi.',
                'duration_minutes'   => 90,
                'attempts_allowed'   => null,
                'result_release'     => null,
                'settings'           => null,
            ],

            Assessment::KIND_ONLINE_TEST => [
                'title'              => 'Ujian Talian 1',
                'instructions'       => 'Anda mempunyai 60 minit untuk menjawab semua soalan setelah memulakan ujian. Hanya satu cubaan dibenarkan.',
                'total_marks'        => 50.00,
                'weight_pct'         => 20.00,
                'open_at'            => $semesterStart->copy()->addWeeks(5),
                'due_at'             => $semesterStart->copy()->addWeeks(7)->setTime(23, 59),
                'late_policy'        => Assessment::LATE_POLICY_NOT_ALLOWED,
                'late_penalty_rules' => null,
                'venue'              => null,
                'allowed_materials'  => null,
                'duration_minutes'   => 60,
                'attempts_allowed'   => 1,
                'result_release'     => Assessment::RESULT_RELEASE_AFTER_WINDOW_CLOSE,
                'settings'           => null,
            ],
        };
    }

    /**
     * Populates the question bank for one online test.
     *   offerings 1-5: 4 MCQ + 2 SA (6 questions)
     *   offerings 6-8: 3 MCQ + 2 SA (5 questions)
     *   offerings 9-15: skipped — shell tests with no questions, exercises empty state
     */
    protected function seedOnlineTestQuestions(Assessment $assessment, int $offeringId, ?int $userId): void
    {
        [$mcqCount, $saCount] = $offeringId <= 5 ? [4, 2] : [3, 2];

        $sortOrder = 0;

        for ($i = 1; $i <= $mcqCount; $i++) {
            $sortOrder++;
            $question = OnlineTestQuestion::updateOrCreate(
                ['assessment_id' => $assessment->id, 'sort_order' => $sortOrder],
                [
                    'kind'               => OnlineTestQuestion::KIND_MCQ,
                    'question_text'      => "Soalan {$sortOrder}: Manakah pernyataan berikut yang BENAR mengenai topik kuliah {$i}?",
                    'marks'              => 5.00,
                    'explanation'        => "Rujuk Bab {$i} dalam buku rujukan utama untuk justifikasi penuh.",
                    'suggested_answer'   => null,
                    'image_disk'         => null,
                    'image_path'         => null,
                    'created_by_user_id' => $userId,
                    'updated_by_user_id' => $userId,
                ]
            );

            // Multi-correct demo: every 13th MCQ by id has 2 correct options.
            $multiCorrect = $question->id % 13 === 0;
            for ($opt = 0; $opt < 4; $opt++) {
                $isCorrect = $multiCorrect
                    ? in_array($opt, [0, 2], true)
                    : $opt === 0;
                OnlineTestQuestionOption::updateOrCreate(
                    ['online_test_question_id' => $question->id, 'sort_order' => $opt],
                    [
                        'option_text' => 'Pilihan ' . chr(65 + $opt) . ': Pernyataan kandidat ' . ($opt + 1),
                        'image_disk'  => null,
                        'image_path'  => null,
                        'is_correct'  => $isCorrect,
                    ]
                );
            }
        }

        for ($i = 1; $i <= $saCount; $i++) {
            $sortOrder++;
            OnlineTestQuestion::updateOrCreate(
                ['assessment_id' => $assessment->id, 'sort_order' => $sortOrder],
                [
                    'kind'               => OnlineTestQuestion::KIND_SHORT_ANSWER,
                    'question_text'      => "Soalan {$sortOrder}: Terangkan secara ringkas (3-5 ayat) konsep utama yang dibincangkan dalam topik {$i}, dan beri satu contoh praktikal.",
                    'marks'              => 10.00,
                    'explanation'        => null,
                    'suggested_answer'   => 'Jawapan model: konsep utama, justifikasi, dan satu contoh praktikal yang relevan dengan konteks pengajaran sekolah rendah.',
                    'image_disk'         => null,
                    'image_path'         => null,
                    'created_by_user_id' => $userId,
                    'updated_by_user_id' => $userId,
                ]
            );
        }
    }

    // ============ Wave 2 Unit D: IPG Leave / MC Requests ============

    /**
     * Seeds 7 leave requests across the 4 v1 statuses with explicit coverage of:
     *   (a) auto_acknowledged response rows on at least one approved request
     *   (b) at least one approved request crossing >=2 Pensyarah's courses with
     *       differing per-course responses (approve_impact + object)
     *   (c) mix of requests with and without supporting documents
     *
     * Pensyarah responses are generated per (request, course_offering) for
     * every offering in the trainee's cohort (implicit cohort-wide enrollment
     * from Unit C). Submitted-but-not-yet-processed requests have NO responses;
     * withdrawn requests have only the responses that came in pre-withdrawal.
     */
    protected function seedIpgLeaveRequests(Campus $campus, Semester $semester): void
    {
        $semesterStart = $semester->start_date;
        $thresholdDays = (int) config('ipg.leave.response_threshold_days', 7);

        $ipgAdmin = User::where('role', 'ipg_admin')->first();

        $trainees = Trainee::where('campus_id', $campus->id)
            ->orderBy('id')
            ->take(7)
            ->get();
        if ($trainees->count() < 7) return;

        // [trainee_idx, kind, status, week_offset, duration_days, has_doc, response_pattern]
        // response_pattern values are interpreted by buildLeaveResponseSpec().
        $requests = [
            [0, IpgLeaveRequest::KIND_MEDICAL,        IpgLeaveRequest::STATUS_SUBMITTED,  9, 2, true,  'none'],
            [1, IpgLeaveRequest::KIND_FAMILY,         IpgLeaveRequest::STATUS_APPROVED,   7, 3, false, 'all_approve'],
            [2, IpgLeaveRequest::KIND_MEDICAL,        IpgLeaveRequest::STATUS_APPROVED,   6, 4, true,  'mostly_approve_one_object'],
            [3, IpgLeaveRequest::KIND_CO_CURRICULAR,  IpgLeaveRequest::STATUS_APPROVED,   5, 2, false, 'partial_explicit_rest_auto'],
            [4, IpgLeaveRequest::KIND_PERSONAL,       IpgLeaveRequest::STATUS_APPROVED,   8, 1, false, 'mixed_ack_approve'],
            [5, IpgLeaveRequest::KIND_OTHER,          IpgLeaveRequest::STATUS_REJECTED,   8, 5, false, 'mostly_object'],
            [6, IpgLeaveRequest::KIND_FAMILY,         IpgLeaveRequest::STATUS_WITHDRAWN, 10, 3, false, 'partial_two_only'],
        ];

        foreach ($requests as [$tIdx, $kind, $status, $weekOffset, $durationDays, $hasDoc, $pattern]) {
            $trainee   = $trainees[$tIdx];
            $startDate = $semesterStart->copy()->addWeeks($weekOffset);
            $endDate   = $startDate->copy()->addDays($durationDays);
            $createdAt = $startDate->copy()->subDays(3);
            $thresholdAt = $createdAt->copy()->addDays($thresholdDays);

            $isDecided = in_array($status, [IpgLeaveRequest::STATUS_APPROVED, IpgLeaveRequest::STATUS_REJECTED], true);

            $request = IpgLeaveRequest::updateOrCreate(
                [
                    'trainee_id' => $trainee->id,
                    'kind'       => $kind,
                    'start_date' => $startDate->toDateString(),
                ],
                [
                    'semester_id'              => $semester->id,
                    'end_date'                 => $endDate->toDateString(),
                    'reason'                   => $this->leaveReasonText($kind),
                    'supporting_document_disk' => $hasDoc ? 'local' : null,
                    'supporting_document_path' => $hasDoc
                        ? 'leave-mc/demo/trainee-' . $trainee->id . '-' . $kind . '-' . $startDate->format('Y-m') . '.pdf'
                        : null,
                    'status'                   => $status,
                    'decided_by_user_id'       => $isDecided ? $ipgAdmin?->id : null,
                    'decided_at'               => $isDecided ? $createdAt->copy()->addDays(2) : null,
                    'decision_notes'           => match ($status) {
                        IpgLeaveRequest::STATUS_APPROVED => 'Permohonan diluluskan berdasarkan dokumen sokongan dan respon pensyarah.',
                        IpgLeaveRequest::STATUS_REJECTED => 'Permohonan ditolak — bantahan pensyarah utama mengenai pertindihan kuiz penting.',
                        default                          => null,
                    },
                    'response_threshold_at'    => $thresholdAt,
                    'created_by_user_id'       => $trainee->user_id,
                    'updated_by_user_id'       => $trainee->user_id,
                ]
            );

            $offerings = CourseOffering::query()
                ->where('semester_id', $semester->id)
                ->where('cohort_id', $trainee->cohort_id)
                ->with('lecturer')
                ->orderBy('id')
                ->get();

            foreach ($offerings as $i => $offering) {
                $spec = $this->buildLeaveResponseSpec($pattern, $i);
                if ($spec === null) continue; // pattern explicitly omits this index

                $isAuto = $spec['auto'] ?? false;
                $respondedAt = $isAuto
                    ? $thresholdAt
                    : $createdAt->copy()->addDays(1 + $i);
                $respondedByUserId = $isAuto ? null : $offering->lecturer?->user_id;

                IpgLeaveRequestPensyarahResponse::updateOrCreate(
                    [
                        'ipg_leave_request_id' => $request->id,
                        'course_offering_id'   => $offering->id,
                    ],
                    [
                        'pensyarah_id'         => $offering->lecturer_pensyarah_id,
                        'response'             => $spec['response'],
                        'conditions'           => $spec['response'] === IpgLeaveRequestPensyarahResponse::RESPONSE_APPROVE_IMPACT
                            ? 'Sila hantar tugasan tertangguh dalam masa 1 minggu selepas kembali ke kelas.'
                            : null,
                        'objection_reason'     => $spec['response'] === IpgLeaveRequestPensyarahResponse::RESPONSE_OBJECT
                            ? 'Tarikh pertindihan dengan kuiz penting yang tidak boleh diubah.'
                            : null,
                        'responded_at'         => $respondedAt,
                        'responded_by_user_id' => $respondedByUserId,
                        'auto_acknowledged'    => $isAuto,
                    ]
                );
            }
        }
    }

    /**
     * Returns the response spec for one (pattern, offering index) tuple, or
     * null when the pattern omits that index.
     *
     * Patterns:
     *   none                          : no responses (status=submitted)
     *   all_approve                   : every offering = approve_impact
     *   mostly_approve_one_object     : 4 approve_impact + 1 object on the last offering
     *   partial_explicit_rest_auto    : first 2 explicit, rest auto-acknowledged
     *   mixed_ack_approve             : first 2 acknowledge, rest approve_impact
     *   mostly_object                 : first 3 object, rest acknowledge
     *   partial_two_only              : only first 2 (approve_impact + acknowledge)
     */
    protected function buildLeaveResponseSpec(string $pattern, int $i): ?array
    {
        $A = IpgLeaveRequestPensyarahResponse::RESPONSE_ACKNOWLEDGE;
        $P = IpgLeaveRequestPensyarahResponse::RESPONSE_APPROVE_IMPACT;
        $O = IpgLeaveRequestPensyarahResponse::RESPONSE_OBJECT;

        return match ($pattern) {
            'none'                       => null,
            'all_approve'                => ['response' => $P],
            'mostly_approve_one_object'  => ['response' => $i === 4 ? $O : $P],
            'partial_explicit_rest_auto' => $i < 2
                ? ['response' => $i === 0 ? $P : $A]
                : ['response' => $A, 'auto' => true],
            'mixed_ack_approve'          => ['response' => $i < 2 ? $A : $P],
            'mostly_object'              => ['response' => $i < 3 ? $O : $A],
            'partial_two_only'           => $i < 2
                ? ['response' => $i === 0 ? $P : $A]
                : null,
            default                      => null,
        };
    }

    protected function leaveReasonText(string $kind): string
    {
        return match ($kind) {
            IpgLeaveRequest::KIND_MEDICAL       => 'Saya kurang sihat. Sijil cuti sakit dari klinik disertakan.',
            IpgLeaveRequest::KIND_PERSONAL      => 'Permohonan cuti atas urusan peribadi yang mendesak.',
            IpgLeaveRequest::KIND_FAMILY        => 'Saya perlu pulang ke kampung kerana keperluan keluarga.',
            IpgLeaveRequest::KIND_CO_CURRICULAR => 'Mewakili IPG dalam pertandingan peringkat zon.',
            IpgLeaveRequest::KIND_OTHER         => 'Permohonan cuti atas sebab lain (sila rujuk dokumen).',
            default                             => '',
        };
    }

    // ============ Wave 2 Unit E: Discipline Categories + Incidents + Cases + Evidence + Witnesses ============

    /** Seeds the 5 default discipline categories from W1.5. Idempotent. */
    protected function seedDisciplineCategories(): void
    {
        $defaults = [
            ['slug' => 'akademik',  'name' => 'Akademik'],
            ['slug' => 'kelakuan',  'name' => 'Kelakuan'],
            ['slug' => 'kehadiran', 'name' => 'Kehadiran'],
            ['slug' => 'etika',     'name' => 'Etika'],
            ['slug' => 'lain-lain', 'name' => 'Lain-lain'],
        ];

        foreach ($defaults as $idx => $cat) {
            DisciplineCategory::updateOrCreate(
                ['slug' => $cat['slug']],
                ['name' => $cat['name'], 'sort_order' => $idx, 'is_active' => true]
            );
        }
    }

    /**
     * Seeds 6 discipline cases covering all 4 statuses, all 3 severities, and
     * the linked-incident scenario. Required scenarios per Q7:
     *   - At least one case with BOTH internal AND external witnesses (Case 3)
     *   - At least one case with NO witnesses AND NO evidence (Case 1)
     *   - Linked pair via shared incident_id (Cases 3 + 4 reference Incident I1)
     */
    protected function seedIpgDisciplineCases(Campus $campus, Semester $semester): void
    {
        $semesterStart = $semester->start_date;
        $ipgAdmin = User::where('role', 'ipg_admin')->first();

        $trainees = Trainee::where('campus_id', $campus->id)->orderBy('id')->take(6)->get();
        if ($trainees->count() < 6) return;

        // Filers — only Pensyarahs with user accounts (1, 2, 3) so created_by_user_id resolves cleanly.
        $filers = Pensyarah::where('campus_id', $campus->id)->whereNotNull('user_id')->orderBy('id')->get();
        if ($filers->isEmpty()) return;

        $categoryBySlug = DisciplineCategory::pluck('id', 'slug');

        // Shared incident for Cases 3 + 4 (peer link, not parent-child).
        $sharedIncident = DisciplineIncident::updateOrCreate(
            [
                'title'       => 'Insiden gangguan kelas Sains Tahun 4',
                'occurred_at' => $semesterStart->copy()->addWeeks(6)->setTime(10, 30),
            ],
            [
                'location'           => 'Bilik Sains 2, Blok B',
                'description'        => 'Dua orang murid mengganggu sesi makmal dengan kelakuan tidak senonoh; dua pensyarah hadir dan memfailkan laporan berasingan.',
                'notes'              => 'Disusulkan dengan kedua-dua laporan pensyarah. Diputuskan bersama-sama oleh IPG Admin.',
                'created_by_user_id' => $ipgAdmin?->id,
                'updated_by_user_id' => $ipgAdmin?->id,
            ]
        );

        $cases = [
            // [trainee_idx, severity, status, category_slug, week_offset, incident, evidence_count, witness_specs[]]
            [
                'trainee_idx'    => 0,
                'severity'       => IpgDisciplineCase::SEVERITY_MINOR,
                'status'         => IpgDisciplineCase::STATUS_SUBMITTED,
                'category'       => 'kelakuan',
                'week_offset'    => 8,
                'incident_id'    => null,
                'description'    => 'Trainee menggunakan telefon bimbit semasa kuliah berlangsung walaupun telah diingatkan.',
                'recommended'    => 'Amaran lisan dan rekod awal kelakuan.',
                'evidence_files' => [],
                'witnesses'      => [],
            ],
            [
                'trainee_idx'    => 1,
                'severity'       => IpgDisciplineCase::SEVERITY_MODERATE,
                'status'         => IpgDisciplineCase::STATUS_UNDER_REVIEW,
                'category'       => 'akademik',
                'week_offset'    => 7,
                'incident_id'    => null,
                'description'    => 'Disyaki menyalin tugasan rakan sekelas. Kandungan tugasan hampir sama perkataan demi perkataan.',
                'recommended'    => 'Soal siasat lanjut dan amaran bertulis.',
                'evidence_files' => [
                    ['filename' => 'case-2-screenshot-tugasan.png', 'mime' => 'image/png', 'size' => 348_000, 'description' => 'Tangkap layar perbandingan tugasan.'],
                ],
                'witnesses'      => [],
            ],
            [
                'trainee_idx'    => 2,
                'severity'       => IpgDisciplineCase::SEVERITY_SERIOUS,
                'status'         => IpgDisciplineCase::STATUS_UNDER_REVIEW,
                'category'       => 'kehadiran',
                'week_offset'    => 6,
                'incident_id'    => $sharedIncident->id,
                'description'    => 'Trainee mengganggu sesi makmal dengan menjerit dan mengganggu eksperimen rakan-rakan; menolak amaran.',
                'recommended'    => 'Penggantungan sementara dan rujukan kepada Penasihat Akademik.',
                'evidence_files' => [
                    ['filename' => 'case-3-photo-makmal.jpg',  'mime' => 'image/jpeg',     'size' => 2_104_000, 'description' => 'Keadaan makmal selepas insiden.'],
                    ['filename' => 'case-3-pernyataan.pdf',    'mime' => 'application/pdf','size' =>   612_000, 'description' => 'Pernyataan bertulis pensyarah penyelaras.'],
                ],
                'witnesses' => [
                    ['kind' => 'internal', 'user_pensyarah_id' => 2],
                    ['kind' => 'external', 'name' => 'En. Razif bin Mohd', 'contact' => 'razif.penjaga@example.com'],
                ],
            ],
            [
                'trainee_idx'    => 3,
                'severity'       => IpgDisciplineCase::SEVERITY_SERIOUS,
                'status'         => IpgDisciplineCase::STATUS_ACTION_TAKEN,
                'category'       => 'kehadiran',
                'week_offset'    => 6,
                'incident_id'    => $sharedIncident->id,
                'description'    => 'Trainee terlibat sama dalam gangguan makmal Sains Tahun 4. Tindakan secara berulang.',
                'recommended'    => 'Amaran bertulis dan kerja khidmat masyarakat 10 jam.',
                'evidence_files' => [
                    ['filename' => 'case-4-photo-tambahan.jpg', 'mime' => 'image/jpeg', 'size' => 1_780_000, 'description' => 'Bukti tambahan dari kamera CCTV.'],
                ],
                'witnesses' => [
                    ['kind' => 'internal', 'user_pensyarah_id' => 3],
                ],
            ],
            [
                'trainee_idx'    => 4,
                'severity'       => IpgDisciplineCase::SEVERITY_MODERATE,
                'status'         => IpgDisciplineCase::STATUS_ACTION_TAKEN,
                'category'       => 'etika',
                'week_offset'    => 5,
                'incident_id'    => null,
                'description'    => 'Trainee bersikap kasar terhadap pengawal keselamatan ketika diminta menunjukkan kad pelajar.',
                'recommended'    => 'Amaran bertulis dan permohonan maaf rasmi.',
                'evidence_files' => [],
                'witnesses' => [
                    ['kind' => 'external', 'name' => 'Cik Diana Ng', 'contact' => '012-3456789'],
                ],
            ],
            [
                'trainee_idx'    => 5,
                'severity'       => IpgDisciplineCase::SEVERITY_MINOR,
                'status'         => IpgDisciplineCase::STATUS_DISMISSED,
                'category'       => 'lain-lain',
                'week_offset'    => 4,
                'incident_id'    => null,
                'description'    => 'Aduan rakan sekelas mengenai bunyi bising di asrama. Setelah disiasat, didapati salah faham.',
                'recommended'    => 'Tiada tindakan; nasihat sahaja.',
                'evidence_files' => [
                    ['filename' => 'case-6-aduan-asrama.pdf', 'mime' => 'application/pdf', 'size' => 248_000, 'description' => 'Salinan aduan asal.'],
                ],
                'witnesses'      => [],
            ],
        ];

        foreach ($cases as $cIdx => $spec) {
            $trainee = $trainees[$spec['trainee_idx']];
            $filer   = $filers[$cIdx % $filers->count()];
            $incidentAt = $semesterStart->copy()->addWeeks($spec['week_offset'])->setTime(10, 0);
            $createdAt  = $incidentAt->copy()->addDays(1);

            $isReviewed = in_array($spec['status'], [
                IpgDisciplineCase::STATUS_UNDER_REVIEW,
                IpgDisciplineCase::STATUS_ACTION_TAKEN,
                IpgDisciplineCase::STATUS_DISMISSED,
            ], true);
            $isDecided = in_array($spec['status'], [
                IpgDisciplineCase::STATUS_ACTION_TAKEN,
                IpgDisciplineCase::STATUS_DISMISSED,
            ], true);

            // Auto-set priority_flag when severity=serious (per W1.5 edge case).
            $priorityFlag = $spec['severity'] === IpgDisciplineCase::SEVERITY_SERIOUS;

            $case = IpgDisciplineCase::updateOrCreate(
                [
                    'trainee_id'  => $trainee->id,
                    'incident_at' => $incidentAt,
                ],
                [
                    'semester_id'             => $semester->id,
                    'discipline_category_id'  => $categoryBySlug[$spec['category']],
                    'incident_id'             => $spec['incident_id'],
                    'severity'                => $spec['severity'],
                    'status'                  => $spec['status'],
                    'description'             => $spec['description'],
                    'recommended_action'      => $spec['recommended'],
                    'filed_by_pensyarah_id'   => $filer->id,
                    'reviewed_by_user_id'     => $isReviewed ? $ipgAdmin?->id : null,
                    'reviewed_at'             => $isReviewed ? $createdAt->copy()->addDays(2) : null,
                    'decided_by_user_id'      => $isDecided ? $ipgAdmin?->id : null,
                    'decided_at'              => $isDecided ? $createdAt->copy()->addDays(5) : null,
                    'decision'                => match ($spec['status']) {
                        IpgDisciplineCase::STATUS_ACTION_TAKEN => 'Tindakan diambil mengikut cadangan pensyarah dengan pengubahsuaian. Trainee dimaklumkan secara rasmi.',
                        IpgDisciplineCase::STATUS_DISMISSED    => 'Selepas siasatan, didapati tiada asas untuk tindakan tatatertib. Kes ditutup.',
                        default                                => null,
                    },
                    'priority_flag'           => $priorityFlag,
                    'created_by_user_id'      => $filer->user_id,
                    'updated_by_user_id'      => $filer->user_id,
                ]
            );

            foreach ($spec['evidence_files'] as $ev) {
                IpgDisciplineCaseEvidence::updateOrCreate(
                    ['ipg_discipline_case_id' => $case->id, 'original_filename' => $ev['filename']],
                    [
                        'disk'                => 'local',
                        'path'                => 'discipline-evidence/demo/' . $ev['filename'],
                        'mime_type'           => $ev['mime'],
                        'size_bytes'          => $ev['size'],
                        'description'         => $ev['description'],
                        'uploaded_by_user_id' => $filer->user_id,
                    ]
                );
            }

            foreach ($spec['witnesses'] as $w) {
                if ($w['kind'] === 'internal') {
                    $witnessUser = Pensyarah::find($w['user_pensyarah_id'])?->user;
                    if ($witnessUser === null) continue; // skip if pensyarah lacks user account
                    IpgDisciplineCaseWitness::updateOrCreate(
                        [
                            'ipg_discipline_case_id' => $case->id,
                            'witness_user_id'        => $witnessUser->id,
                        ],
                        [
                            'witness_name'         => null,
                            'witness_contact'      => null,
                            'statement'            => 'Saya menyaksikan insiden tersebut secara langsung dari jarak dekat.',
                            'recorded_by_user_id'  => $filer->user_id,
                        ]
                    );
                } else { // external
                    IpgDisciplineCaseWitness::updateOrCreate(
                        [
                            'ipg_discipline_case_id' => $case->id,
                            'witness_name'           => $w['name'],
                        ],
                        [
                            'witness_user_id'      => null,
                            'witness_contact'      => $w['contact'],
                            'statement'            => 'Saya hadir di tempat kejadian dan menyaksikan apa yang berlaku.',
                            'recorded_by_user_id'  => $filer->user_id,
                        ]
                    );
                }
            }
        }
    }

    // ============ Phase 4: Academics ============

    protected function seedAcademics(Campus $campus, Semester $semester): void
    {
        // Courses (PISMP semester load, ~5 courses).
        $courseDefs = [
            ['code' => 'EDUP-3013', 'title' => 'Falsafah & Pendidikan di Malaysia', 'credit_hours' => 3],
            ['code' => 'MTE-3023',  'title' => 'Pengajaran Matematik Sekolah Rendah', 'credit_hours' => 3],
            ['code' => 'BMM-3013',  'title' => 'Pengajaran Bahasa Melayu Sekolah Rendah', 'credit_hours' => 3],
            ['code' => 'SCE-3013',  'title' => 'Pengajaran Sains Sekolah Rendah', 'credit_hours' => 3],
            ['code' => 'EDUP-3033', 'title' => 'Penyelidikan Tindakan I', 'credit_hours' => 2],
        ];
        $courses = collect();
        foreach ($courseDefs as $cd) {
            $courses->push(Course::firstOrCreate(
                ['campus_id' => $campus->id, 'code' => $cd['code']],
                ['title' => $cd['title'], 'credit_hours' => $cd['credit_hours']]
            ));
        }

        // Transcript entries — for the first 8 trainees, 4 random courses with realistic grades.
        $gradeBuckets = [
            ['letter' => 'A',  'point' => 4.00],
            ['letter' => 'A-', 'point' => 3.67],
            ['letter' => 'B+', 'point' => 3.33],
            ['letter' => 'B',  'point' => 3.00],
            ['letter' => 'B-', 'point' => 2.67],
            ['letter' => 'C+', 'point' => 2.33],
        ];

        $trainees = Trainee::where('campus_id', $campus->id)->orderBy('id')->take(8)->get();
        foreach ($trainees as $idx => $trainee) {
            // Each trainee gets results in 4 of the 5 courses, deterministically picked.
            foreach ($courses->take(4) as $cIdx => $course) {
                $gradeIdx = ($idx + $cIdx) % count($gradeBuckets);
                $bucket = $gradeBuckets[$gradeIdx];
                TranscriptEntry::firstOrCreate(
                    ['trainee_id' => $trainee->id, 'semester_id' => $semester->id, 'course_id' => $course->id],
                    ['grade_letter' => $bucket['letter'], 'grade_point' => $bucket['point']]
                );
            }
        }

        // Co-curriculum activities (mandatory for PISMP).
        $activityDefs = [
            ['code' => 'KOK-OLA',  'name' => 'Kelab Olahraga',           'category' => 'sukan',         'max_units' => 3],
            ['code' => 'KOK-PUS',  'name' => 'Persatuan Bahasa Melayu',  'category' => 'persatuan',     'max_units' => 3],
            ['code' => 'KOK-KEM',  'name' => 'Persatuan Pencinta Alam',  'category' => 'kelab',         'max_units' => 3],
            ['code' => 'KOK-KOR',  'name' => 'Kor Sukarelawan Polis',    'category' => 'beruniform',    'max_units' => 3],
        ];
        $activities = collect();
        foreach ($activityDefs as $ad) {
            $activities->push(CocurricularActivity::firstOrCreate(
                ['campus_id' => $campus->id, 'code' => $ad['code']],
                ['name' => $ad['name'], 'category' => $ad['category'], 'max_units' => $ad['max_units']]
            ));
        }

        // ~10 participations across trainees.
        foreach ($trainees as $idx => $trainee) {
            // Each trainee joins 1-2 activities.
            $picks = [$activities[$idx % $activities->count()]];
            if ($idx % 2 === 0 && $idx > 0) {
                $picks[] = $activities[($idx + 1) % $activities->count()];
            }
            foreach ($picks as $aIdx => $activity) {
                $role = ['member', 'secretary', 'vice_president', 'president'][$idx % 4];
                $units = [1, 2, 3][$idx % 3];
                $score = 60 + (($idx * 7 + $aIdx * 11) % 40); // 60..99
                CocurricularParticipation::firstOrCreate(
                    [
                        'trainee_id'  => $trainee->id,
                        'activity_id' => $activity->id,
                        'semester_id' => $semester->id,
                    ],
                    [
                        'role'             => $role,
                        'units_earned'     => $units,
                        'evaluation_score' => $score,
                    ]
                );
            }
        }

        // Research projects — one per final-year trainee (we'll mark the first 6).
        $supervisors = Pensyarah::where('campus_id', $campus->id)->orderBy('id')->get();
        $statuses = ['proposal', 'in_progress', 'in_progress', 'submitted', 'submitted', 'evaluated'];

        $researchTopics = [
            'Kesan Pembelajaran Berasaskan Permainan dalam Matematik Tahap 1',
            'Penggunaan Bacaan Bertema untuk Murid Tahun 4 dalam Bahasa Melayu',
            'Pendekatan STEM dalam Pengajaran Sains Sekolah Rendah',
            'Strategi Pembelajaran Diferensiasi untuk Kelas Berbilang-aras',
            'Penggunaan Realiti Augmentasi dalam Pengajaran Geografi',
            'Kesan Pemulihan Awal terhadap Pencapaian Murid Tahun 1',
        ];

        foreach ($trainees->take(6) as $idx => $trainee) {
            $startedAt  = now()->subMonths(6 - ($idx % 4));
            $submittedAt = $statuses[$idx] === 'submitted' || $statuses[$idx] === 'evaluated'
                ? $startedAt->copy()->addMonths(4)
                : null;
            $score = $statuses[$idx] === 'evaluated' ? 70 + (($idx * 5) % 25) : null;

            ResearchProject::updateOrCreate(
                ['trainee_id' => $trainee->id],
                [
                    'supervisor_pensyarah_id' => $supervisors[$idx % $supervisors->count()]->id ?? null,
                    'title'                   => $researchTopics[$idx],
                    'status'                  => $statuses[$idx],
                    'started_at'              => $startedAt,
                    'submitted_at'            => $submittedAt,
                    'evaluation_score'        => $score,
                    'milestones'              => [
                        ['label' => 'Cadangan kajian',       'due_date' => $startedAt->copy()->addMonths(1)->toDateString(), 'done_at' => $startedAt->copy()->addDays(28)->toDateString()],
                        ['label' => 'Pengumpulan data',      'due_date' => $startedAt->copy()->addMonths(3)->toDateString(), 'done_at' => $statuses[$idx] !== 'proposal' ? $startedAt->copy()->addMonths(3)->toDateString() : null],
                        ['label' => 'Penulisan laporan',     'due_date' => $startedAt->copy()->addMonths(5)->toDateString(), 'done_at' => in_array($statuses[$idx], ['submitted', 'evaluated'], true) ? $startedAt->copy()->addMonths(5)->toDateString() : null],
                        ['label' => 'Pembentangan akhir',    'due_date' => $startedAt->copy()->addMonths(6)->toDateString(), 'done_at' => $statuses[$idx] === 'evaluated' ? $startedAt->copy()->addMonths(6)->toDateString() : null],
                    ],
                ]
            );
        }
    }

    // ============ Phase 5: Practicum ============

    protected function seedPracticum(Campus $campus, Semester $semester, ?PracticumWindow $window = null): void
    {
        $this->seedPracticumData($campus, $semester, $window);
    }

    protected function seedPracticumData(Campus $campus, Semester $semester, ?PracticumWindow $window = null): void
    {
        $school = School::first();
        if (! $school) return;

        $coordinator = Pensyarah::where('campus_id', $campus->id)->where('is_practicum_coordinator', true)->first();
        $supervisors = Pensyarah::where('campus_id', $campus->id)->where('is_practicum_coordinator', false)->orderBy('id')->get();
        if ($supervisors->isEmpty()) return;

        // Pick 4 trainees from different cohorts to place at the host school.
        $trainees = Trainee::where('campus_id', $campus->id)->orderBy('id')->take(4)->get();

        $now = now();
        // Cover the new state machine (W3.4): one of each meaningful pre-completion
        // status, plus an in-progress 'active' so PracticumProjection still shows
        // visible data in the host school's School-mode view.
        $statuses = [
            Placement::STATUS_ACTIVE,                  // idx 0: in-progress at host school
            Placement::STATUS_CONFIRMED,               // idx 1: principal acknowledged, not yet started
            Placement::STATUS_COMPLETED,               // idx 2: window closed, evaluation submitted
            Placement::STATUS_PENDING_ACKNOWLEDGEMENT, // idx 3: letter dispatched, awaiting principal
        ];

        foreach ($trainees as $idx => $trainee) {
            // Stagger windows so 'active' is mid-window and 'confirmed' is near-future.
            $offsetWeeks = match ($statuses[$idx]) {
                Placement::STATUS_COMPLETED               => -16, // ended 4 weeks ago
                Placement::STATUS_ACTIVE                  => -3,  // 3 weeks in
                Placement::STATUS_CONFIRMED               => 1,   // starts in 1 week
                Placement::STATUS_PENDING_ACKNOWLEDGEMENT => 2,   // starts in 2 weeks
                default                                   => -3,
            };
            $start = $now->copy()->addWeeks($offsetWeeks);
            $end   = $start->copy()->addWeeks(12);

            $placement = Placement::updateOrCreate(
                ['trainee_id' => $trainee->id, 'semester_id' => $semester->id],
                [
                    'host_school_id'           => $school->id,
                    'practicum_window_id'      => $window?->id,
                    'supervisor_pensyarah_id'  => $supervisors[$idx % $supervisors->count()]->id,
                    'start_date'               => $start->toDateString(),
                    'end_date'                 => $end->toDateString(),
                    'subjects'                 => ['Matematik', 'Bahasa Melayu'],
                    'levels'                   => ['Tahun 4', 'Tahun 5'],
                    'status'                   => $statuses[$idx],
                ]
            );

            // 2 observations per placement
            for ($i = 1; $i <= 2; $i++) {
                \App\Models\Observation::updateOrCreate(
                    ['placement_id' => $placement->id, 'observed_at' => $start->copy()->addWeeks($i * 3)->toDateString()],
                    [
                        'lesson_topic'                  => 'Lesson '.$i.': Pengenalan ' . (['pecahan','ayat majmuk'][$i % 2]),
                        'notes'                         => 'Teknik penerangan jelas; perlu meningkatkan pengurusan masa.',
                        'rubric_score'                  => 70 + (($idx * 4 + $i * 3) % 25),
                        'evaluated_by_pensyarah_id'     => $supervisors[$idx % $supervisors->count()]->id,
                    ]
                );
            }

            // Logbook entries (4 weekly reflections)
            for ($w = 1; $w <= 4; $w++) {
                \App\Models\LogbookEntry::updateOrCreate(
                    ['placement_id' => $placement->id, 'week_number' => $w],
                    [
                        'reflection_text'           => "Minggu {$w}: pengalaman mengajar dan refleksi pengurusan kelas.",
                        'submitted_at'              => $start->copy()->addWeeks($w),
                        'reviewed_by_pensyarah_id'  => $w <= 3 ? $supervisors[$idx % $supervisors->count()]->id : null,
                        'review_comment'            => $w <= 3 ? 'Refleksi konkrit; teruskan.' : null,
                        'reviewed_at'               => $w <= 3 ? $start->copy()->addWeeks($w)->addDays(2) : null,
                    ]
                );
            }

            // Evaluation only for completed placements
            if ($statuses[$idx] === 'completed') {
                \App\Models\Evaluation::updateOrCreate(
                    ['placement_id' => $placement->id],
                    [
                        'score'                     => 78 + (($idx * 3) % 18),
                        'grade_letter'              => 'B+',
                        'comments'                  => 'Keseluruhan prestasi memuaskan. Penambahbaikan disarankan dalam pengurusan tingkah laku.',
                        'evaluated_by_pensyarah_id' => $coordinator?->id ?? $supervisors[$idx % $supervisors->count()]->id,
                        'evaluated_at'              => $end->copy()->addDays(7),
                    ]
                );
            }

            // Placement letter (correspondence record)
            \App\Models\PlacementLetter::updateOrCreate(
                ['placement_id' => $placement->id],
                [
                    'kind'              => 'placement_letter',
                    'principal_name'    => 'En. Ahmad Tarmizi bin Hashim',
                    'sent_at'           => $start->copy()->subWeeks(2),
                    'acknowledged_at'   => $idx < 3 ? $start->copy()->subWeeks(2)->addDays(3) : null,
                    'body'              => "Sukacita dimaklumkan bahawa Guru Pelatih {$trainee->name} ({$trainee->trainee_number}) akan menjalani Praktikum Fasa 2 di sekolah tuan dari {$start->format('d M Y')} sehingga {$end->format('d M Y')}.",
                ]
            );
        }
    }

    // ============ Phase 7: Hostel ============

    protected function seedHostel(Campus $campus, Semester $semester): void
    {
        $blockDefs = [
            ['code' => 'KEDIDI', 'name' => 'Asrama Kediaman Didi'],
            ['code' => 'JAMBU',  'name' => 'Asrama Bunga Jambu'],
        ];
        $blocks = collect();
        foreach ($blockDefs as $bd) {
            $blocks->push(\App\Models\HostelBlock::updateOrCreate(
                ['campus_id' => $campus->id, 'code' => $bd['code']],
                ['name' => $bd['name'], 'capacity' => 32]
            ));
        }

        // 4 rooms per block, 2 trainees per room.
        $rooms = collect();
        foreach ($blocks as $block) {
            foreach (range(1, 4) as $n) {
                $rooms->push(\App\Models\HostelRoom::updateOrCreate(
                    ['block_id' => $block->id, 'room_number' => sprintf('%s-%02d', $block->code, $n)],
                    ['capacity' => 2]
                ));
            }
        }

        // Assignments — 8 trainees into rooms.
        $trainees = Trainee::where('campus_id', $campus->id)->orderBy('id')->take(8)->get();
        foreach ($trainees as $idx => $trainee) {
            \App\Models\HostelAssignment::updateOrCreate(
                ['trainee_id' => $trainee->id, 'semester_id' => $semester->id],
                [
                    'room_id' => $rooms[$idx % $rooms->count()]->id,
                    'status'  => 'active',
                ]
            );
        }
    }
}
