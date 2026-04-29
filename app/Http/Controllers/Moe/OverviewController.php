<?php

namespace App\Http\Controllers\Moe;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSnapshot;
use App\Models\Camera;
use App\Models\Event;
use App\Models\School;
use Illuminate\Http\Request;

class OverviewController extends Controller
{
    public function index(Request $request)
    {
        $schools = School::all();
        $incidents = Event::selectRaw('school_id, severity, count(*) as n')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy('school_id', 'severity')->get()->groupBy('school_id');
        $uptime = Camera::selectRaw('school_id, sum(online) as online, count(*) as total')
            ->groupBy('school_id')->get()->keyBy('school_id');
        $attendanceAgg = AttendanceSnapshot::selectRaw('school_id, status, count(*) as n')
            ->whereDate('date', '>=', now()->subDays(7))
            ->groupBy('school_id', 'status')->get()->groupBy('school_id');
        return view('moe.overview', compact('schools', 'incidents', 'uptime', 'attendanceAgg'));
    }
}
