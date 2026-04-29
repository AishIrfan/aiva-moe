<?php

namespace App\Http\Controllers\School;

use App\Models\AttendanceSnapshot;
use App\Models\Camera;
use App\Models\Event;
use App\Models\Student;
use Illuminate\Http\Request;

class OverviewController extends SchoolContextController
{
    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $today = now()->toDateString();

        $studentCount = Student::where('school_id', $school->id)->count();
        $cameras = Camera::where('school_id', $school->id)->get();
        $openEvents = Event::where('school_id', $school->id)
            ->where('status', 'open')->latest()->limit(10)->get();
        $attendance = AttendanceSnapshot::where('school_id', $school->id)
            ->where('date', $today)
            ->selectRaw('status, count(*) as n')
            ->groupBy('status')->pluck('n', 'status');

        $hotZones = Event::where('school_id', $school->id)
            ->whereNotNull('zone_id')
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->selectRaw('zone_id, count(*) as n')
            ->groupBy('zone_id')->orderByDesc('n')->limit(5)->with('zone')->get();

        return view('school.overview', compact('school', 'studentCount', 'cameras', 'openEvents', 'attendance', 'hotZones'));
    }
}
