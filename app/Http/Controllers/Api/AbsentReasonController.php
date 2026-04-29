<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsentReason;
use Illuminate\Http\Request;

class AbsentReasonController extends Controller
{
    use ApiResponder;

    public function index() { return $this->ok(AbsentReason::orderBy('order')->get()); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'unique:absent_reasons,code'],
            'label' => ['required', 'string'],
            'is_excused' => ['boolean'],
            'counts_as_present' => ['boolean'],
        ]);
        return $this->ok(AbsentReason::create($data));
    }

    public function show(AbsentReason $absentReason) { return $this->ok($absentReason); }
    public function update(Request $request, AbsentReason $absentReason) { $absentReason->update($request->only(['label', 'is_excused', 'counts_as_present', 'order'])); return $this->ok($absentReason); }
    public function destroy(AbsentReason $absentReason) { $absentReason->delete(); return $this->ok(null); }
}
