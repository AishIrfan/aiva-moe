<?php

namespace App\Http\Controllers\School;

use App\Models\DisciplineAction;
use App\Models\DisciplineCase;
use App\Models\DisciplineEvidence;
use App\Services\AuditLogger;
use App\Services\DisciplineService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DisciplineController extends SchoolContextController
{
    public function __construct(private DisciplineService $service) {}

    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $tab = $request->get('tab', 'list');
        $q = DisciplineCase::where('school_id', $school->id)->with(['student', 'actions', 'evidence']);
        $q = match ($tab) {
            'pending' => $q->where('status', 'pending_review'),
            'reports' => $q->whereIn('status', ['resolved', 'closed']),
            default => $q,
        };
        $cases = $q->latest()->paginate(30)->withQueryString();
        return view('school.laporan-masalah-disiplin', compact('school', 'cases', 'tab'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'category' => ['required', 'in:bullying,absenteeism,misconduct,uniform,other'],
            'severity' => ['required', 'in:low,medium,high'],
            'incident_date' => ['required', 'date'],
            'location' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);
        $school = $this->requireSchool($request);
        $case = DisciplineCase::create(array_merge($data, [
            'school_id' => $school->id,
            'reported_by' => $request->user()->id,
            'case_number' => 'DISC-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
            'status' => 'submitted',
        ]));
        AuditLogger::log('discipline.create', $case, [], $data);
        return back()->with('status', 'Case submitted.');
    }

    public function transition(Request $request, DisciplineCase $case)
    {
        $this->ensureOwned($request, $case);
        $data = $request->validate([
            'status' => ['required', 'in:submitted,pending_review,under_investigation,action_required,resolved,closed,rejected,cancelled'],
        ]);
        if (! $this->service->canTransition($case, $data['status'])) {
            return back()->withErrors(['status' => 'Invalid transition.']);
        }
        $case->update($data);
        AuditLogger::log('discipline.transition', $case, [], $data);
        return back()->with('status', 'Status updated.');
    }

    public function addAction(Request $request, DisciplineCase $case)
    {
        $this->ensureOwned($request, $case);
        $data = $request->validate([
            'type' => ['required', 'in:warning,suspension,counseling_referral,parent_call,detention,other'],
            'note' => ['nullable', 'string'],
        ]);
        $action = DisciplineAction::create(array_merge($data, [
            'discipline_case_id' => $case->id,
            'actor_id' => $request->user()->id,
            'taken_at' => now(),
        ]));
        AuditLogger::log('discipline.action', $action, [], $data);
        return back()->with('status', 'Action recorded.');
    }

    public function addEvidence(Request $request, DisciplineCase $case)
    {
        $this->ensureOwned($request, $case);
        $request->validate(['file' => ['required', 'file', 'max:10240']]);
        $file = $request->file('file');
        $ev = DisciplineEvidence::create([
            'discipline_case_id' => $case->id,
            'original_name' => $file->getClientOriginalName(),
            'path' => $file->store("discipline/{$case->id}"),
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
        ]);
        AuditLogger::log('discipline.evidence', $ev);
        return back()->with('status', 'Evidence uploaded.');
    }
}
