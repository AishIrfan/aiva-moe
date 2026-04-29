<?php

namespace App\Http\Controllers\Moe;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\School;
use Illuminate\Http\Request;

class TrendsController extends Controller
{
    public function index(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $trend = Event::whereDate('created_at', '>=', now()->subDays($days))
            ->selectRaw("date(created_at) as day, type, count(*) as n")
            ->groupBy('day', 'type')->get()->groupBy('day');

        $perSchool = Event::whereDate('created_at', '>=', now()->subDays($days))
            ->selectRaw('school_id, count(*) as n')
            ->groupBy('school_id')->orderByDesc('n')->get();

        $schoolNames = School::whereIn('id', $perSchool->pluck('school_id'))->pluck('name', 'id');

        return view('moe.trends', compact('trend', 'perSchool', 'schoolNames', 'days'));
    }
}
