<?php

namespace Tests\Feature;

use App\Jobs\FlushFrImage;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FrIngestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_fr_trigger_stores_event_and_dispatches_flush(): void
    {
        Queue::fake();
        $school = School::create(['name' => 'T', 'code' => 'T']);

        $payload = [
            'school_id' => $school->id,
            'external_event_id' => 'EXT-1',
            'person_id' => 'p1',
            'person_name' => 'Alice',
            'image_url' => 'https://example.invalid/img.jpg',
            'confidence' => 0.97,
            'attributes' => ['age' => '20'],
            'trigger_targets' => [['type' => 'notify', 'ref' => 'admin']],
        ];

        $this->postJson('/api/fr/trigger', $payload)
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.person_name', 'Alice');

        $this->assertDatabaseHas('fr_events', ['person_id' => 'p1']);
        $this->assertDatabaseHas('fr_event_attributes', ['attribute_key' => 'age']);
        Queue::assertPushed(FlushFrImage::class);
    }
}
