<?php

namespace App\Http\Controllers\BPG;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use Illuminate\Http\Request;

/**
 * BPG (Bahagian Pendidikan Guru) — ministry-level surface for IPG mode.
 *
 * Parallel to the MOE controllers in concept: this is what BPG admins see when
 * they log into AIVA. They live inside IPG mode (session.mode = 'ipg') and
 * navigate ministry-wide views before drilling into a specific campus.
 *
 * v1 surface mirrors the MOE module — Overview / Campuses / Trends — and is
 * scaffolded against the IPG placeholder template until we wire real BPG data.
 */
class BPGController extends Controller
{
    public function overview()
    {
        return view('bpg.overview', [
            'campusCount' => Campus::count(),
        ]);
    }

    public function campuses()
    {
        return view('bpg.campuses', [
            'campuses' => Campus::orderBy('name')->get(),
        ]);
    }

    public function selectCampus(Request $request)
    {
        $data = $request->validate(['campus_id' => ['required', 'exists:campuses,id']]);
        $campus = Campus::findOrFail($data['campus_id']);

        $request->session()->put('campus_id',   $campus->id);
        $request->session()->put('campus_name', $campus->name);
        $request->session()->put('mode',        'ipg');

        return redirect()->route('ipg.overview');
    }

    public function trends()
    {
        return view('bpg.trends');
    }
}
