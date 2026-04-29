<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        return redirect()->route(match ($user->defaultMode()) {
            'moe' => 'moe.overview',
            'ipg' => 'ipg.overview',
            default => 'school.overview',
        });
    }
}
