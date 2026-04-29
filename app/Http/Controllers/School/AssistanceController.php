<?php

namespace App\Http\Controllers\School;

use App\Models\AssistanceApplication;
use App\Models\AssistanceProgram;
use App\Models\Student;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssistanceController extends SchoolContextController
{
    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $programs = AssistanceProgram::where('school_id', $school->id)->orderByDesc('id')->get();
        $applications = AssistanceApplication::whereIn('assistance_program_id', $programs->pluck('id'))
            ->with(['student', 'program'])
            ->latest()->paginate(25);
        $students = Student::where('school_id', $school->id)->orderBy('name')->get();
        return view('school.assistance', compact('school', 'programs', 'applications', 'students'));
    }

    public function storeProgram(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['nullable', 'string', 'max:40'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'opens_on' => ['nullable', 'date'],
            'closes_on' => ['nullable', 'date'],
        ]);
        $school = $this->requireSchool($request);
        $program = AssistanceProgram::create(array_merge($data, ['school_id' => $school->id]));
        AuditLogger::log('assistance.program.create', $program, [], $data);
        return back()->with('status', 'Program created.');
    }

    public function updateProgram(Request $request, AssistanceProgram $program)
    {
        $this->ensureOwned($request, $program);
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'status' => ['sometimes', 'in:active,paused,archived'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
        ]);
        $before = $program->only(array_keys($data));
        $program->update($data);
        AuditLogger::log('assistance.program.update', $program, $before, $data);
        return back()->with('status', 'Program updated.');
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'assistance_program_id' => ['required', 'exists:assistance_programs,id'],
            'student_id' => ['required', 'exists:students,id'],
            'requested_amount' => ['nullable', 'numeric', 'min:0'],
            'household_data' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
        ]);
        $app = AssistanceApplication::create(array_merge($data, [
            'submitted_by' => $request->user()->id,
            'status' => 'submitted',
        ]));
        AuditLogger::log('assistance.submit', $app, [], $data);
        return back()->with('status', 'Application submitted.');
    }

    public function verify(Request $request, AssistanceApplication $application)
    {
        $this->ensureOwned($request, $application, 'program');
        $application->update([
            'status' => 'verified',
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);
        AuditLogger::log('assistance.verify', $application, [], []);
        return back()->with('status', 'Application verified.');
    }

    public function decide(Request $request, AssistanceApplication $application)
    {
        $this->ensureOwned($request, $application, 'program');
        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'approved_amount' => ['nullable', 'numeric', 'min:0'],
        ]);
        $application->update([
            'status' => $data['decision'],
            'decided_by' => $request->user()->id,
            'decided_at' => now(),
            'approved_amount' => $data['approved_amount'] ?? null,
        ]);
        AuditLogger::log('assistance.decide', $application, [], $data);
        return back()->with('status', 'Decision saved.');
    }

    public function disburse(Request $request, AssistanceApplication $application)
    {
        $this->ensureOwned($request, $application, 'program');
        $application->update(['status' => 'disbursed', 'disbursed_at' => now()]);
        AuditLogger::log('assistance.disburse', $application, [], []);
        return back()->with('status', 'Disbursement marked.');
    }

    public function export(Request $request): StreamedResponse
    {
        $school = $this->requireSchool($request);
        $programIds = AssistanceProgram::where('school_id', $school->id)->pluck('id');
        $apps = AssistanceApplication::whereIn('assistance_program_id', $programIds)
            ->with(['student', 'program'])->get();

        $filename = 'assistance-'.$school->id.'-'.now()->format('YmdHis').'.csv';
        return response()->streamDownload(function () use ($apps) {
            $fh = fopen('php://output', 'w');
            fputcsv($fh, ['Program', 'Student', 'Status', 'Requested', 'Approved', 'Submitted At']);
            foreach ($apps as $a) {
                fputcsv($fh, [$a->program->name, $a->student->name, $a->status, $a->requested_amount, $a->approved_amount, $a->created_at]);
            }
            fclose($fh);
        }, $filename);
    }
}
