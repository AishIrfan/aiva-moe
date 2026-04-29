<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class SchoolContextController extends Controller
{
    protected function schoolId(Request $request): ?int
    {
        return $request->session()->get('school_id') ?? $request->user()?->school_id;
    }

    protected function school(Request $request): ?School
    {
        $id = $this->schoolId($request);
        return $id ? School::find($id) : null;
    }

    protected function requireSchool(Request $request): School
    {
        $school = $this->school($request);
        if ($school) {
            return $school;
        }

        // MOE admins should pick a school before entering school mode.
        if ($request->user()?->isMoe()) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()->route('moe.schools')->with('status', 'Pick a school to enter school mode.')
            );
        }

        abort(403, 'No school is associated with your account. Contact an administrator.');
    }

    /**
     * Verify a route-bound model belongs to the current school.
     * Traverses one hop through a relation if the model doesn't carry school_id directly
     * (e.g. Message → Conversation → school_id, DisciplineAction → DisciplineCase → school_id).
     */
    protected function ensureOwned(Request $request, Model $model, string $relationPath = ''): void
    {
        $schoolId = $this->requireSchool($request)->id;
        $target = $model;
        if ($relationPath !== '') {
            foreach (explode('.', $relationPath) as $rel) {
                $target = $target?->{$rel};
                if ($target === null) abort(404);
            }
        }
        if ((int) ($target->school_id ?? 0) !== $schoolId) {
            abort(404);
        }
    }
}
