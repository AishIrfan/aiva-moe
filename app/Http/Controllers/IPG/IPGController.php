<?php

namespace App\Http\Controllers\IPG;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\CocurricularActivity;
use App\Models\CocurricularParticipation;
use App\Models\Cohort;
use App\Models\Course;
use App\Models\Evaluation;
use App\Models\HostelAssignment;
use App\Models\HostelBlock;
use App\Models\HostelRoom;
use App\Models\LogbookEntry;
use App\Models\Observation;
use App\Models\Pensyarah;
use App\Models\Placement;
use App\Models\PlacementLetter;
use App\Models\Program;
use App\Models\ResearchProject;
use App\Models\Semester;
use App\Models\Trainee;
use App\Models\TranscriptEntry;
use Illuminate\Http\Request;

/**
 * IPG (Institut Pendidikan Guru) module controller.
 *
 * Route surface aligned with IPG_MODE_CHECKLIST.md §7. As of Phase 3, the
 * RENAMED §3 modules and KEEP §4 modules render dedicated views (with real
 * seed data where available, IPG-flavored empty states otherwise). The
 * genuinely-new §5/§6 modules (Transcripts, Co-curriculum, Research, Hostel,
 * Practicum × 6) still render the shared `ipg.placeholder` template until
 * each is built out in Phases 4–7.
 *
 * Action endpoints (POST/PUT/DELETE) hit `stub()` until each module is wired.
 */
class IPGController extends Controller
{
    /**
     * Page metadata for slugs that still use the shared placeholder template.
     * Promoted modules are absent from this map and have their own dedicated
     * handler methods below.
     */
    /**
     * Page metadata for slugs that still use the shared placeholder template.
     * As of Phase 7, every IPG page is promoted — this map is intentionally empty
     * but kept for future use if any page ever needs to fall back to the template.
     */
    protected const PAGES = [];

    protected function render(string $slug)
    {
        if (! isset(self::PAGES[$slug])) {
            abort(404);
        }
        [$section, $title, $blurb] = self::PAGES[$slug];

        return view('ipg.placeholder', [
            'pageSlug' => $slug,
            'section'  => $section,
            'title'    => $title,
            'blurb'    => $blurb,
        ]);
    }

    /** Resolve the active campus from session, or null when none picked. */
    protected function activeCampus(): ?Campus
    {
        $id = session('campus_id');
        return $id ? Campus::find($id) : null;
    }

    // ============ Promoted handlers (§3 renames + §4 KEEP modules) ============

    public function overview()
    {
        return view('ipg.overview');
    }

    public function academicCalendar()
    {
        $campus = $this->activeCampus();
        return view('ipg.academic-calendar', [
            'campus'  => $campus,
            'current' => $campus?->currentSemester(),
        ]);
    }

    public function gradesCohorts()
    {
        $campus = $this->activeCampus();

        $cohorts = $campus
            ? Cohort::where('campus_id', $campus->id)->with('program')->orderBy('major')->get()
            : collect();

        $traineeCounts = $campus
            ? Trainee::selectRaw('cohort_id, count(*) as n')
                ->where('campus_id', $campus->id)
                ->whereNotNull('cohort_id')
                ->groupBy('cohort_id')
                ->pluck('n', 'cohort_id')
                ->toArray()
            : [];

        return view('ipg.grades-cohorts', [
            'cohorts'       => $cohorts,
            'programs'      => Program::orderBy('code')->get(),
            'traineeCounts' => $traineeCounts,
        ]);
    }

    public function enrollment()
    {
        $campus = $this->activeCampus();
        return view('ipg.enrollment', [
            'cohorts'   => $campus ? Cohort::where('campus_id', $campus->id)->with('program')->get() : collect(),
            'semesters' => $campus ? Semester::where('campus_id', $campus->id)->orderByDesc('start_date')->get() : collect(),
        ]);
    }

    public function timetables()
    {
        $campus = $this->activeCampus();
        return view('ipg.timetables', [
            'cohorts'    => $campus ? Cohort::where('campus_id', $campus->id)->with('program')->get() : collect(),
            'pensyarahs' => $campus ? Pensyarah::where('campus_id', $campus->id)->orderBy('name')->get() : collect(),
        ]);
    }

    public function trainees(Request $request)
    {
        $campus = $this->activeCampus();

        $q = Trainee::query()->with('cohort.program');
        if ($campus) {
            $q->where('campus_id', $campus->id);
        }
        if ($search = $request->get('q')) {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                  ->orWhere('trainee_number', 'like', "%{$search}%")
                  ->orWhere('ic_number', 'like', "%{$search}%");
            });
        }
        if ($cohortId = $request->get('cohort_id')) {
            $q->where('cohort_id', $cohortId);
        }

        return view('ipg.trainees', [
            'campus'   => $campus,
            'trainees' => $q->orderBy('name')->paginate(50)->withQueryString(),
            'cohorts'  => $campus ? Cohort::where('campus_id', $campus->id)->with('program')->orderBy('major')->get() : collect(),
        ]);
    }

    public function trainee360(Request $request)
    {
        $campus = $this->activeCampus();
        $user   = $request->user();

        // Trainee role: lock to their own profile regardless of query string.
        if ($user?->isTrainee() && $user->trainee) {
            return view('ipg.trainee-360', ['trainee' => $user->trainee->load('cohort.program')]);
        }

        $id = $request->get('trainee');
        $trainee = null;
        if ($id) {
            $trainee = Trainee::with('cohort.program')->find($id);
        } elseif ($campus) {
            $trainee = Trainee::where('campus_id', $campus->id)->with('cohort.program')->first();
        }

        return view('ipg.trainee-360', compact('trainee'));
    }

    public function leaves()
    {
        $campus = $this->activeCampus();
        return view('ipg.leaves', [
            'trainees' => $campus ? Trainee::where('campus_id', $campus->id)->orderBy('name')->get() : collect(),
        ]);
    }

    public function assistance()
    {
        $campus = $this->activeCampus();
        return view('ipg.assistance', [
            'trainees' => $campus ? Trainee::where('campus_id', $campus->id)->orderBy('name')->get() : collect(),
        ]);
    }

    public function chat()                       { return view('ipg.chat'); }
    public function documents()                  { return view('ipg.documents'); }
    public function eventsManagement()           { return view('ipg.events-management'); }
    public function suratCutiMc()                { return view('ipg.surat-cuti-mc'); }
    public function laporanDisiplin()            { return view('ipg.laporan-masalah-disiplin'); }
    public function reports()                    { return view('ipg.reports'); }
    public function settings()                   { return view('ipg.settings'); }

    // Attendance + 4 sub-pages
    public function attendance()                 { return view('ipg.attendance'); }
    public function attendanceFollowUp()         { return view('ipg.attendance-follow-up'); }
    public function attendanceRecords()          { return view('ipg.attendance-records'); }
    public function attendanceMonthlySummary()   { return view('ipg.attendance-monthly-summary'); }
    public function attendanceWarningLetters()   { return view('ipg.attendance-warning-letters'); }

    // ============ Phase 4 — Academics (NEW) ============

    public function transcripts(Request $request)
    {
        $campus = $this->activeCampus();
        $user   = $request->user();
        if (! $campus) {
            return view('ipg.transcripts', [
                'campus'   => null,
                'trainee'  => null,
                'trainees' => collect(),
                'entries'  => collect(),
            ]);
        }

        // Trainee role: lock to their own transcript and hide the trainee picker.
        if ($user?->isTrainee() && $user->trainee) {
            $trainee  = $user->trainee->load('cohort.program');
            $trainees = collect([$trainee]);
            $entries  = TranscriptEntry::with('course', 'semester')
                ->where('trainee_id', $trainee->id)
                ->orderBy('semester_id')
                ->get()
                ->groupBy('semester_id');
            return view('ipg.transcripts', compact('campus', 'trainee', 'trainees', 'entries'));
        }

        $traineeId = $request->get('trainee');
        $trainee   = $traineeId
            ? Trainee::with('cohort.program')->where('campus_id', $campus->id)->find($traineeId)
            : Trainee::with('cohort.program')->where('campus_id', $campus->id)
                ->whereHas('transcriptEntries')
                ->orderBy('id')->first();

        $trainees  = Trainee::where('campus_id', $campus->id)
            ->whereHas('transcriptEntries')
            ->orderBy('name')
            ->get();

        $entries = $trainee
            ? TranscriptEntry::with('course', 'semester')
                ->where('trainee_id', $trainee->id)
                ->orderBy('semester_id')
                ->get()
                ->groupBy('semester_id')
            : collect();

        return view('ipg.transcripts', [
            'campus'   => $campus,
            'trainee'  => $trainee,
            'trainees' => $trainees,
            'entries'  => $entries,
        ]);
    }

    public function cocurriculum()
    {
        $campus = $this->activeCampus();
        return view('ipg.cocurriculum', [
            'campus'         => $campus,
            'activities'     => $campus ? CocurricularActivity::where('campus_id', $campus->id)->withCount('participations')->orderBy('category')->orderBy('name')->get() : collect(),
            'participations' => $campus
                ? CocurricularParticipation::with('trainee', 'activity', 'semester')
                    ->whereHas('trainee', fn ($q) => $q->where('campus_id', $campus->id))
                    ->latest('id')
                    ->take(20)
                    ->get()
                : collect(),
        ]);
    }

    public function research()
    {
        $campus = $this->activeCampus();
        return view('ipg.research', [
            'campus'   => $campus,
            'projects' => $campus
                ? ResearchProject::with('trainee.cohort.program', 'supervisor')
                    ->whereHas('trainee', fn ($q) => $q->where('campus_id', $campus->id))
                    ->orderBy('status')
                    ->get()
                : collect(),
        ]);
    }

    // ============ Phase 5 — Practicum ============
    // All queries route through Placement::scopeVisibleTo($user), which enforces
    // §6.2 visibility: Pensyarah without coordinator flag see only their own
    // assigned trainees; coordinator/IPG/BPG/MOE see everything campus-wide.

    public function practicumPlacements(Request $request)
    {
        $campus = $this->activeCampus();
        $user   = $request->user();

        $placements = Placement::query()
            ->visibleTo($user)
            ->when($campus, fn ($q) => $q->whereHas('trainee', fn ($qq) => $qq->where('campus_id', $campus->id)))
            ->with(['trainee.cohort.program', 'hostSchool', 'supervisor', 'semester'])
            ->orderBy('start_date', 'desc')
            ->get();

        return view('ipg.practicum.placements', [
            'campus'     => $campus,
            'placements' => $placements,
        ]);
    }

    public function practicumSupervisors(Request $request)
    {
        $campus = $this->activeCampus();
        if (! $campus) {
            return view('ipg.practicum.supervisors', [
                'campus'      => null,
                'supervisors' => collect(),
            ]);
        }

        $supervisors = Pensyarah::where('campus_id', $campus->id)
            ->withCount(['placements as assigned_count' => function ($q) {
                $q->where('status', '!=', 'cancelled');
            }])
            ->orderByDesc('is_practicum_coordinator')
            ->orderBy('name')
            ->get();

        return view('ipg.practicum.supervisors', [
            'campus'      => $campus,
            'supervisors' => $supervisors,
        ]);
    }

    public function practicumObservations(Request $request)
    {
        $campus = $this->activeCampus();
        $user   = $request->user();

        $observations = Observation::query()
            ->whereHas('placement', fn ($q) => $q->visibleTo($user)->when($campus, fn ($qq) => $qq->whereHas('trainee', fn ($qqq) => $qqq->where('campus_id', $campus->id))))
            ->with(['placement.trainee', 'placement.hostSchool', 'evaluator'])
            ->orderByDesc('observed_at')
            ->get();

        return view('ipg.practicum.observations', [
            'campus'       => $campus,
            'observations' => $observations,
        ]);
    }

    public function practicumEvaluations(Request $request)
    {
        $campus = $this->activeCampus();
        $user   = $request->user();

        $evaluations = Evaluation::query()
            ->whereHas('placement', fn ($q) => $q->visibleTo($user)->when($campus, fn ($qq) => $qq->whereHas('trainee', fn ($qqq) => $qqq->where('campus_id', $campus->id))))
            ->with(['placement.trainee.cohort.program', 'evaluator'])
            ->orderByDesc('evaluated_at')
            ->get();

        return view('ipg.practicum.evaluations', [
            'campus'      => $campus,
            'evaluations' => $evaluations,
        ]);
    }

    public function practicumLogbook(Request $request)
    {
        $campus = $this->activeCampus();
        $user   = $request->user();

        $entries = LogbookEntry::query()
            ->whereHas('placement', fn ($q) => $q->visibleTo($user)->when($campus, fn ($qq) => $qq->whereHas('trainee', fn ($qqq) => $qqq->where('campus_id', $campus->id))))
            ->with(['placement.trainee', 'reviewer'])
            ->orderByDesc('week_number')
            ->orderByDesc('submitted_at')
            ->get();

        return view('ipg.practicum.logbook', [
            'campus'  => $campus,
            'entries' => $entries,
        ]);
    }

    public function practicumCoordination(Request $request)
    {
        $campus = $this->activeCampus();
        $user   = $request->user();

        $letters = PlacementLetter::query()
            ->whereHas('placement', fn ($q) => $q->visibleTo($user)->when($campus, fn ($qq) => $qq->whereHas('trainee', fn ($qqq) => $qqq->where('campus_id', $campus->id))))
            ->with(['placement.trainee', 'placement.hostSchool'])
            ->orderByDesc('sent_at')
            ->get();

        return view('ipg.practicum.coordination', [
            'campus'  => $campus,
            'letters' => $letters,
        ]);
    }

    // ============ Phase 7 — Hostel ============

    public function hostel()
    {
        $campus = $this->activeCampus();
        if (! $campus) {
            return view('ipg.hostel', [
                'campus'        => null,
                'blocks'        => collect(),
                'assignments'   => collect(),
                'totalCapacity' => 0,
                'occupied'      => 0,
                'occupancyPct'  => 0,
            ]);
        }

        $blocks = HostelBlock::where('campus_id', $campus->id)
            ->withCount(['rooms'])
            ->with(['rooms' => function ($q) {
                $q->withCount(['assignments as occupants_count' => function ($qq) {
                    $qq->where('status', 'active');
                }]);
            }])
            ->orderBy('name')
            ->get();

        $assignments = HostelAssignment::query()
            ->where('status', 'active')
            ->whereHas('trainee', fn ($q) => $q->where('campus_id', $campus->id))
            ->with(['trainee.cohort.program', 'room.block', 'semester'])
            ->orderBy('id')
            ->get();

        $totalCapacity = $blocks->sum(fn ($b) => $b->rooms->sum('capacity'));
        $occupied      = $assignments->count();
        $occupancyPct  = $totalCapacity > 0 ? round(($occupied / $totalCapacity) * 100, 1) : 0;

        return view('ipg.hostel', [
            'campus'        => $campus,
            'blocks'        => $blocks,
            'assignments'   => $assignments,
            'totalCapacity' => $totalCapacity,
            'occupied'      => $occupied,
            'occupancyPct'  => $occupancyPct,
        ]);
    }

    /**
     * Universal stub for action endpoints (POST/PUT/DELETE) until they're wired.
     */
    public function stub(Request $request)
    {
        return back()->with('status', 'IPG action received — module is currently a scaffold and not yet wired.');
    }
}
