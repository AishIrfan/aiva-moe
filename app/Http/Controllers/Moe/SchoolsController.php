<?php

namespace App\Http\Controllers\Moe;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolsController extends Controller
{
    public function index(Request $request)
    {
        $q = School::query();
        if ($search = $request->get('q')) $q->where('name', 'like', "%{$search}%");
        if ($state = $request->get('state')) $q->where('state', $state);
        $sort = $request->get('sort', 'name');
        $schools = $q->orderBy($sort)->paginate(50)->withQueryString();
        $incidentCounts = Event::whereIn('school_id', $schools->pluck('id'))
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->selectRaw('school_id, count(*) as n')
            ->groupBy('school_id')->pluck('n', 'school_id');
        return view('moe.schools', compact('schools', 'incidentCounts'));
    }

    public function select(Request $request)
    {
        $data = $request->validate(['school_id' => ['required', 'exists:schools,id']]);
        $school = School::findOrFail($data['school_id']);
        $request->session()->put('school_id', $school->id);
        $request->session()->put('school_name', $school->name);
        $request->session()->put('mode', 'school');
        return redirect()->route('school.overview');
    }
}
