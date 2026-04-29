<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Student;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $students = collect();
        $events = collect();
        if (strlen($q) >= 2) {
            $students = Student::query()
                ->where('name', 'like', "%{$q}%")
                ->orWhere('student_number', 'like', "%{$q}%")
                ->limit(20)->get();
            $events = Event::query()
                ->where('title', 'like', "%{$q}%")
                ->latest()->limit(20)->get();
        }
        return view('search', compact('q', 'students', 'events'));
    }
}
