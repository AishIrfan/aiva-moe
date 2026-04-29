<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModeController extends Controller
{
    public function switch(Request $request)
    {
        $data = $request->validate([
            'mode' => ['required', Rule::in(['school', 'moe', 'ipg'])],
        ]);

        $user = $request->user();

        if ($data['mode'] === 'moe' && ! $user->canSwitchMode()) {
            abort(403, 'Not allowed to switch to MOE mode.');
        }
        if ($data['mode'] === 'ipg' && ! ($user->livesInIpgMode() || $user->canSwitchMode())) {
            abort(403, 'Not allowed to switch to IPG mode.');
        }
        if ($data['mode'] === 'school' && $user->livesInIpgMode() && ! $user->canSwitchMode()) {
            abort(403, 'Not allowed to switch to School mode.');
        }

        $request->session()->put('mode', $data['mode']);

        return redirect()->route(match ($data['mode']) {
            'moe' => 'moe.overview',
            'ipg' => 'ipg.overview',
            default => 'school.overview',
        });
    }
}
