<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    use ApiResponder;

    public function index() { return $this->ok(School::all()); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'code' => ['nullable', 'string', 'unique:schools,code'],
            'state' => ['nullable', 'string'],
        ]);
        return $this->ok(School::create($data));
    }

    public function show(School $school) { return $this->ok($school); }

    public function update(Request $request, School $school)
    {
        $school->update($request->only(['name', 'code', 'state', 'district', 'address', 'phone', 'principal']));
        return $this->ok($school);
    }

    public function destroy(School $school)
    {
        $school->delete();
        return $this->ok(null);
    }
}
