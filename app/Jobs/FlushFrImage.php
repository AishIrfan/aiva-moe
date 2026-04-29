<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlushFrImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $imageUrl, public int $frEventId) {}

    public function handle(): void
    {
        $endpoint = env('FR_IMAGE_FLUSH_URL');
        if (! $endpoint) {
            Log::debug('FR image flush endpoint not configured, skipping.', ['event' => $this->frEventId]);
            return;
        }
        try {
            Http::timeout(30)->post($endpoint, [
                'image_url' => $this->imageUrl,
                'event_id' => $this->frEventId,
            ]);
        } catch (\Throwable $e) {
            Log::warning('FR image flush failed', ['err' => $e->getMessage(), 'event' => $this->frEventId]);
            $this->release(60);
        }
    }
}
