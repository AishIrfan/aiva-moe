<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\FlushFrImage;
use App\Models\FrEvent;
use App\Models\FrEventAttribute;
use App\Models\FrEventTriggerTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FrEventTriggerController extends Controller
{
    use ApiResponder;

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_id' => ['nullable', 'exists:schools,id'],
            'camera_id' => ['nullable', 'exists:cameras,id'],
            'external_event_id' => ['nullable', 'string'],
            'person_id' => ['nullable', 'string'],
            'person_name' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'confidence' => ['nullable', 'numeric'],
            'detected_at' => ['nullable', 'date'],
            'attributes' => ['nullable', 'array'],
            'trigger_targets' => ['nullable', 'array'],
            'payload' => ['nullable', 'array'],
        ]);

        $event = DB::transaction(function () use ($data) {
            $event = FrEvent::create([
                'school_id' => $data['school_id'] ?? null,
                'camera_id' => $data['camera_id'] ?? null,
                'external_event_id' => $data['external_event_id'] ?? null,
                'person_id' => $data['person_id'] ?? null,
                'person_name' => $data['person_name'] ?? null,
                'image_url' => $data['image_url'] ?? null,
                'confidence' => $data['confidence'] ?? null,
                'detected_at' => $data['detected_at'] ?? now(),
                'payload' => $data['payload'] ?? null,
            ]);
            foreach (($data['attributes'] ?? []) as $k => $v) {
                FrEventAttribute::create([
                    'fr_event_id' => $event->id,
                    'attribute_key' => $k,
                    'attribute_value' => is_scalar($v) ? (string) $v : json_encode($v),
                ]);
            }
            foreach (($data['trigger_targets'] ?? []) as $tt) {
                FrEventTriggerTarget::create([
                    'fr_event_id' => $event->id,
                    'target_type' => $tt['type'] ?? 'notify',
                    'target_ref' => $tt['ref'] ?? null,
                    'payload' => $tt['payload'] ?? null,
                ]);
            }
            return $event;
        });

        if ($key = env('FIREBASE_LATEST_EVENT_KEY')) {
            cache()->put($key, $event->id, now()->addMinutes(5));
            Log::debug('FR latest-event cache updated', ['key' => $key, 'event_id' => $event->id]);
        }

        if ($event->image_url) {
            FlushFrImage::dispatch($event->image_url, $event->id);
        }

        return $this->ok($event);
    }
}
