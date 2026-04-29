<?php

namespace App\Http\Controllers\School;

use App\Models\Document;
use App\Models\DocumentAcknowledgment;
use App\Models\DocumentLink;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\AuditLogger;
use App\Services\PracticumProjection;
use Illuminate\Http\Request;

class DocumentsController extends SchoolContextController
{
    public function index(Request $request, PracticumProjection $practicum)
    {
        $school = $this->requireSchool($request);
        $documents = Document::where('school_id', $school->id)->with(['links', 'acknowledgments'])->latest()->paginate(25);
        $classes = SchoolClass::where('school_id', $school->id)->get();

        // §6.1 cross-mode projection: surface IPG placement letters addressed
        // to this host school during the active practicum window.
        $placementLetters = $practicum->lettersForSchool($school->id);

        return view('school.documents', compact('school', 'documents', 'classes', 'placementLetters'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:20480'],
            'requires_ack' => ['nullable', 'boolean'],
        ]);
        $school = $this->requireSchool($request);
        $path = $request->file('file')?->store("schools/{$school->id}/documents");
        $doc = Document::create([
            'school_id' => $school->id,
            'uploaded_by' => $request->user()->id,
            'title' => $data['title'],
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'file_path' => $path,
            'mime_type' => $request->file('file')?->getClientMimeType(),
            'requires_ack' => (bool) ($data['requires_ack'] ?? false),
        ]);
        AuditLogger::log('document.create', $doc, [], $doc->only(['title', 'category']));
        return back()->with('status', 'Document created.');
    }

    public function update(Request $request, Document $document)
    {
        $this->ensureOwned($request, $document);
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:200'],
            'category' => ['sometimes', 'required', 'string', 'max:60'],
            'requires_ack' => ['nullable', 'boolean'],
        ]);
        $before = $document->only(array_keys($data));
        $document->update($data);
        AuditLogger::log('document.update', $document, $before, $data);
        return back()->with('status', 'Document updated.');
    }

    public function link(Request $request, Document $document)
    {
        $this->ensureOwned($request, $document);
        $data = $request->validate([
            'linkable_type' => ['required', 'in:class,student'],
            'linkable_id' => ['required', 'integer'],
        ]);
        $type = $data['linkable_type'] === 'class' ? SchoolClass::class : Student::class;
        DocumentLink::create([
            'document_id' => $document->id,
            'linkable_type' => $type,
            'linkable_id' => $data['linkable_id'],
        ]);
        AuditLogger::log('document.link', $document, [], $data);
        return back()->with('status', 'Linked.');
    }

    public function unlink(Request $request, Document $document)
    {
        $this->ensureOwned($request, $document);
        $data = $request->validate([
            'linkable_type' => ['required', 'in:class,student'],
            'linkable_id' => ['required', 'integer'],
        ]);
        $type = $data['linkable_type'] === 'class' ? SchoolClass::class : Student::class;
        DocumentLink::where('document_id', $document->id)
            ->where('linkable_type', $type)
            ->where('linkable_id', $data['linkable_id'])->delete();
        AuditLogger::log('document.unlink', $document, [], $data);
        return back()->with('status', 'Unlinked.');
    }

    public function ack(Request $request, Document $document)
    {
        $this->ensureOwned($request, $document);
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'guardian_id' => ['nullable', 'exists:guardians,id'],
            'method' => ['required', 'in:student,guardian,digital'],
        ]);
        $ack = DocumentAcknowledgment::updateOrCreate(
            ['document_id' => $document->id, 'student_id' => $data['student_id']],
            [
                'guardian_id' => $data['guardian_id'] ?? null,
                'method' => $data['method'],
                'acknowledged_at' => now(),
            ]
        );
        AuditLogger::log('document.ack', $ack, [], $data);
        return back()->with('status', 'Acknowledged.');
    }
}
