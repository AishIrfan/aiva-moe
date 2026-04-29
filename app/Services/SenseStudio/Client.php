<?php

namespace App\Services\SenseStudio;

use App\Models\Setting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class Client
{
    private string $baseUrl;
    private ?string $token = null;

    public function __construct(?int $schoolId = null)
    {
        $cfg = Setting::schoolValue($schoolId, 'sensestudio', []);
        $this->baseUrl = rtrim((string) ($cfg['base_url'] ?? config('services.sensestudio.base_url', env('SENSESTUDIO_BASE_URL', ''))), '/');
        $this->token = $cfg['token'] ?? null;
    }

    public function authenticate(string $username, string $password): string
    {
        $this->ensureBase();
        $res = Http::post($this->baseUrl.'/auth/login', compact('username', 'password'));
        if (! $res->successful()) throw new RuntimeException('SenseStudio auth failed: '.$res->body());
        $this->token = $res->json('token') ?? $res->json('access_token');
        return $this->token;
    }

    public function ping(): bool
    {
        if (! $this->baseUrl) return false;
        try { return Http::timeout(5)->get($this->baseUrl.'/health')->successful(); }
        catch (\Throwable) { return false; }
    }

    public function createLibrary(string $name): array
    {
        return $this->http()->post($this->baseUrl.'/libraries', ['name' => $name])->throw()->json();
    }

    public function createPerson(string $libraryId, array $payload): array
    {
        return $this->http()->post($this->baseUrl."/libraries/{$libraryId}/persons", $payload)->throw()->json();
    }

    public function enrollFace(string $libraryId, string $personId, string $imagePath): array
    {
        return $this->http()
            ->attach('image', file_get_contents($imagePath), basename($imagePath))
            ->post($this->baseUrl."/libraries/{$libraryId}/persons/{$personId}/faces")
            ->throw()->json();
    }

    public function removePerson(string $libraryId, string $personId): array
    {
        return $this->http()->delete($this->baseUrl."/libraries/{$libraryId}/persons/{$personId}")->throw()->json();
    }

    public function queryEvents(array $filters = []): array
    {
        return $this->http()->get($this->baseUrl.'/events', $filters)->throw()->json();
    }

    private function http(): PendingRequest
    {
        $this->ensureBase();
        return Http::acceptJson()
            ->when($this->token, fn ($h) => $h->withToken($this->token))
            ->timeout(30);
    }

    private function ensureBase(): void
    {
        if (! $this->baseUrl) {
            throw new RuntimeException('SenseStudio base URL not configured. Set SENSESTUDIO_BASE_URL or use School Settings.');
        }
    }
}
