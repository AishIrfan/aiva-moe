<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FrEvent;
use Illuminate\Http\Request;

class FrEventController extends Controller
{
    use ApiResponder;

    public function index(Request $request)
    {
        $q = FrEvent::with(['attributes', 'triggerTargets']);
        if ($sid = $request->get('school_id')) $q->where('school_id', $sid);
        if ($pid = $request->get('person_id')) $q->where('person_id', $pid);
        if ($since = $request->get('since')) $q->where('detected_at', '>=', $since);
        return $this->ok($q->latest('detected_at')->limit(500)->get());
    }
}
