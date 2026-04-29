<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetMode
{
    public function handle(Request $request, Closure $next)
    {
        $session = $request->session();
        $user    = $request->user();

        // Auto-sync session.mode with the URL the user is on so sidebar + UI stay coherent.
        // Note: /bpg/* lives inside IPG mode (BPG is the ministry layer above campuses).
        if ($request->is('moe', 'moe/*')) {
            $session->put('mode', 'moe');
        } elseif ($request->is('ipg', 'ipg/*', 'bpg', 'bpg/*')) {
            $session->put('mode', 'ipg');
        } elseif ($request->is('school', 'school/*')) {
            $session->put('mode', 'school');
        } elseif (! $session->has('mode')) {
            $session->put('mode', $user?->defaultMode() ?? 'school');
        }

        // Seed session.school_id from the user record once per session.
        if (! $session->has('school_id') && $user?->school_id) {
            $session->put('school_id', $user->school_id);
            $session->put('school_name', $user->school?->name);
        }

        // Seed session.campus_id from the user record once per session.
        // BPG admins typically have null campus_id and must pick one (mirrors MOE → school flow).
        if (! $session->has('campus_id') && $user?->campus_id) {
            $session->put('campus_id', $user->campus_id);
            $session->put('campus_name', $user->campus?->name);
        }

        view()->share('currentMode', $session->get('mode'));
        view()->share('currentSchoolId', $session->get('school_id'));
        view()->share('currentCampusId', $session->get('campus_id'));

        return $next($request);
    }
}
