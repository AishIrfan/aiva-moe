<?php

namespace App\Http\Controllers\School;

use App\Models\AttendanceSnapshot;
use App\Models\Event;
use App\Models\Report;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class ReportsController extends SchoolContextController
{
    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $reports = Report::where('school_id', $school->id)->latest()->paginate(25);
        return view('school.reports', compact('school', 'reports'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:attendance,incident,analytics,custom'],
            'title' => ['required', 'string', 'max:200'],
            'period' => ['nullable', 'string', 'max:20'],
        ]);
        $school = $this->requireSchool($request);

        $payload = match ($data['type']) {
            'attendance' => $this->attendanceSummary($school->id, $data['period'] ?? now()->format('Y-m')),
            'incident'   => $this->incidentSummary($school->id, $data['period'] ?? now()->format('Y-m')),
            default      => [],
        };

        $report = Report::create(array_merge($data, [
            'school_id' => $school->id,
            'user_id' => $request->user()->id,
            'data' => $payload,
        ]));
        AuditLogger::log('report.create', $report, [], $data);
        return back()->with('status', 'Report generated.');
    }

    private function attendanceSummary(int $schoolId, string $period): array
    {
        [$y, $m] = explode('-', $period);
        $start = sprintf('%04d-%02d-01', $y, $m);
        $end = date('Y-m-t', strtotime($start));
        return AttendanceSnapshot::where('school_id', $schoolId)
            ->whereBetween('date', [$start, $end])
            ->selectRaw('status, count(*) as n')
            ->groupBy('status')->pluck('n', 'status')->all();
    }

    private function incidentSummary(int $schoolId, string $period): array
    {
        [$y, $m] = explode('-', $period);
        return Event::where('school_id', $schoolId)
            ->whereYear('created_at', $y)
            ->whereMonth('created_at', $m)
            ->selectRaw('type, severity, count(*) as n')
            ->groupBy('type', 'severity')->get()->toArray();
    }
}
