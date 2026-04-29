<?php

namespace App\Http\Controllers\School;

use App\Models\LeaveAttachment;
use App\Models\LeaveSubmission;
use App\Services\AuditLogger;
use App\Services\LeaveSubmissionService;
use Illuminate\Http\Request;

class LeaveManagementController extends SchoolContextController
{
    public function __construct(private LeaveSubmissionService $service) {}

    public function index(Request $request)
    {
        $school = $this->requireSchool($request);
        $tab = $request->get('tab', 'list');
        $q = LeaveSubmission::where('school_id', $school->id)->with(['student', 'reviewer', 'attachments']);
        $q = match ($tab) {
            'pending' => $q->where('status', 'pending_review'),
            'history' => $q->whereIn('status', ['approved', 'rejected', 'cancelled']),
            default => $q,
        };
        $submissions = $q->latest()->paginate(30)->withQueryString();
        return view('school.surat-cuti-mc', compact('school', 'submissions', 'tab'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'category' => ['required', 'in:cuti,mc'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);
        if ($this->service->hasOverlap($data['student_id'], $data['from_date'], $data['to_date'])) {
            return back()->withErrors(['overlap' => 'Leave overlaps existing submission.']);
        }
        $school = $this->requireSchool($request);
        $sub = LeaveSubmission::create(array_merge($data, [
            'school_id' => $school->id,
            'submitted_by' => $request->user()->id,
            'status' => 'submitted',
            'day_count' => $this->service->dayCount($data['from_date'], $data['to_date']),
        ]));
        AuditLogger::log('leave-submission.create', $sub, [], $data);
        return back()->with('status', 'Submitted.');
    }

    public function transition(Request $request, LeaveSubmission $submission)
    {
        $this->ensureOwned($request, $submission);
        $data = $request->validate([
            'status' => ['required', 'in:submitted,pending_review,approved,rejected,cancelled,returned_for_revision'],
            'review_note' => ['nullable', 'string'],
        ]);
        if (! $this->service->canTransition($submission, $data['status'])) {
            return back()->withErrors(['status' => 'Invalid transition.']);
        }
        $submission->update([
            'status' => $data['status'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_note' => $data['review_note'] ?? null,
        ]);
        AuditLogger::log('leave-submission.transition', $submission, [], $data);
        return back()->with('status', 'Status updated.');
    }

    public function addAttachment(Request $request, LeaveSubmission $submission)
    {
        $this->ensureOwned($request, $submission);
        $request->validate(['file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png']]);
        $file = $request->file('file');
        $path = $file->store('leaves/'.$submission->id);
        $att = LeaveAttachment::create([
            'leave_submission_id' => $submission->id,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
        ]);
        AuditLogger::log('leave-submission.attach', $att);
        return back()->with('status', 'Attachment uploaded.');
    }
}
