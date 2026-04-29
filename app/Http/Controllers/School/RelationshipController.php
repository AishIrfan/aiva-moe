<?php

namespace App\Http\Controllers\School;

use App\Models\Event;
use App\Models\Student;
use Illuminate\Http\Request;

class RelationshipController extends SchoolContextController
{
    public function index(Request $request)
    {
        $school = $this->requireSchool($request);

        $students = Student::where('school_id', $school->id)->limit(200)->get();
        $events = Event::where('school_id', $school->id)
            ->whereNotNull('student_id')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->get();

        $nodes = [];
        $edges = [];
        foreach ($students as $s) $nodes[] = ['id' => 'student-'.$s->id, 'label' => $s->name, 'kind' => 'student'];
        foreach ($events as $e) {
            $nodes[] = ['id' => 'event-'.$e->id, 'label' => $e->title, 'kind' => 'event'];
            $edges[] = ['from' => 'student-'.$e->student_id, 'to' => 'event-'.$e->id];
        }

        return view('school.relationship', compact('school', 'nodes', 'edges'));
    }
}
