<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BatEvent;
use App\Models\BatEventAttribute;
use App\Models\BatEventTriggerTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatEventTriggerController extends Controller
{
    use ApiResponder;

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_id' => ['nullable', 'exists:schools,id'],
            'camera_id' => ['nullable', 'exists:cameras,id'],
            'external_event_id' => ['nullable', 'string'],
            'behavior_type' => ['nullable', 'string'],
            'crowd_count' => ['nullable', 'integer'],
            'score' => ['nullable', 'numeric'],
            'detected_at' => ['nullable', 'date'],
            'attributes' => ['nullable', 'array'],
            'trigger_targets' => ['nullable', 'array'],
            'payload' => ['nullable', 'array'],
        ]);

        return $this->ok(DB::transaction(function () use ($data) {
            $event = BatEvent::create([
                'school_id' => $data['school_id'] ?? null,
                'camera_id' => $data['camera_id'] ?? null,
                'external_event_id' => $data['external_event_id'] ?? null,
                'behavior_type' => $data['behavior_type'] ?? null,
                'crowd_count' => $data['crowd_count'] ?? null,
                'score' => $data['score'] ?? null,
                'detected_at' => $data['detected_at'] ?? now(),
                'payload' => $data['payload'] ?? null,
            ]);
            foreach (($data['attributes'] ?? []) as $k => $v) {
                BatEventAttribute::create([
                    'bat_event_id' => $event->id,
                    'attribute_key' => $k,
                    'attribute_value' => is_scalar($v) ? (string) $v : json_encode($v),
                ]);
            }
            foreach (($data['trigger_targets'] ?? []) as $tt) {
                BatEventTriggerTarget::create([
                    'bat_event_id' => $event->id,
                    'target_type' => $tt['type'] ?? 'notify',
                    'target_ref' => $tt['ref'] ?? null,
                    'payload' => $tt['payload'] ?? null,
                ]);
            }
            return $event;
        }));
    }
}
