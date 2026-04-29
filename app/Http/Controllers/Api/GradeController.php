<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    use ApiResponder;

    public function index(Request $request)
    {
        $q = Grade::query();
        if ($sid = $request->get('school_id')) $q->where('school_id', $sid);
        return $this->ok($q->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'name' => ['required', 'string'],
            'level' => ['required', 'integer'],
        ]);
        return $this->ok(Grade::create($data));
    }

    public function show(Grade $grade) { return $this->ok($grade); }
    public function update(Request $request, Grade $grade) { $grade->update($request->only(['name', 'level'])); return $this->ok($grade); }
    public function destroy(Grade $grade) { $grade->delete(); return $this->ok(null); }
}
