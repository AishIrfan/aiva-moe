<?php

namespace App\Http\Controllers\School;

use App\Models\Setting;
use App\Models\Student;
use App\Services\AuditLogger;
use App\Services\SenseStudio\Client;
use Illuminate\Http\Request;

class FaceEnrollmentController extends SchoolContextController
{
    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $students = Student::where('school_id', $school->id)->orderBy('name')->paginate(50);
        $settings = Setting::schoolValue($school->id, 'sensestudio', []);
        $connected = (bool) ($settings['base_url'] ?? false);
        return view('school.face-enrollment', compact('school', 'students', 'settings', 'connected'));
    }

    public function createPerson(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'library_id' => ['required', 'string'],
            'image' => ['required', 'file', 'image', 'max:8192'],
        ]);
        $school = $this->requireSchool($request);
        $student = Student::findOrFail($data['student_id']);

        $client = new Client($school->id);
        try {
            $person = $client->createPerson($data['library_id'], [
                'external_id' => (string) $student->id,
                'name' => $student->name,
                'meta' => ['student_number' => $student->student_number],
            ]);
            $client->enrollFace($data['library_id'], $person['id'] ?? $person['person_id'], $request->file('image')->getPathname());
            AuditLogger::log('face.enroll', $student, [], ['library_id' => $data['library_id']]);
            return back()->with('status', 'Enrolled successfully.');
        } catch (\Throwable $e) {
            return back()->withErrors(['face' => $e->getMessage()]);
        }
    }

    public function removePerson(Request $request, string $personId)
    {
        $data = $request->validate(['library_id' => ['required', 'string']]);
        $school = $this->requireSchool($request);
        try {
            (new Client($school->id))->removePerson($data['library_id'], $personId);
            AuditLogger::log('face.remove', null, [], ['library_id' => $data['library_id'], 'person_id' => $personId]);
            return back()->with('status', 'Person removed.');
        } catch (\Throwable $e) {
            return back()->withErrors(['face' => $e->getMessage()]);
        }
    }
}
