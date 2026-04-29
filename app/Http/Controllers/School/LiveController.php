<?php

namespace App\Http\Controllers\School;

use App\Models\Camera;
use App\Models\Event;
use Illuminate\Http\Request;

class LiveController extends SchoolContextController
{
    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $cameras = Camera::where('school_id', $school->id)->with('zone')->get();
        $recentEvents = Event::where('school_id', $school->id)->latest()->limit(20)->get();
        return view('school.live', compact('school', 'cameras', 'recentEvents'));
    }
}
