<?php

namespace App\Http\Controllers\School;

use App\Models\AttendanceSnapshot;
use App\Models\Event;
use Illuminate\Http\Request;

class AnalyticsController extends SchoolContextController
{
    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $byType = Event::where('school_id', $school->id)
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->selectRaw('type, count(*) as n')->groupBy('type')->pluck('n', 'type');
        $bySeverity = Event::where('school_id', $school->id)
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->selectRaw('severity, count(*) as n')->groupBy('severity')->pluck('n', 'severity');
        $attendanceTrend = AttendanceSnapshot::where('school_id', $school->id)
            ->whereDate('date', '>=', now()->subDays(14))
            ->selectRaw('date, status, count(*) as n')
            ->groupBy('date', 'status')->get()
            ->groupBy('date');
        return view('school.analytics', compact('school', 'byType', 'bySeverity', 'attendanceTrend'));
    }
}
