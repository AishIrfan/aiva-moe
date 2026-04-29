<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BatEvent;
use Illuminate\Http\Request;

class BatEventController extends Controller
{
    use ApiResponder;

    public function index(Request $request)
    {
        $q = BatEvent::with(['attributes', 'triggerTargets']);
        if ($sid = $request->get('school_id')) $q->where('school_id', $sid);
        if ($bt = $request->get('behavior_type')) $q->where('behavior_type', $bt);
        if ($since = $request->get('since')) $q->where('detected_at', '>=', $since);
        $rows = $q->latest('detected_at')->limit(500)->get();

        $metrics = [
            'policy_counts' => $rows->groupBy('behavior_type')->map->count(),
            'crowd_max' => $rows->max('crowd_count'),
            'crowd_avg' => $rows->avg('crowd_count'),
        ];
        return $this->ok($rows, ['metrics' => $metrics]);
    }
}
