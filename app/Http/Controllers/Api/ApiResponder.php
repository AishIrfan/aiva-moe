<?php

namespace App\Http\Controllers\Api;

trait ApiResponder
{
    protected function ok(mixed $data = null, array $extra = []): array
    {
        return array_merge(['ok' => true, 'data' => $data], $extra);
    }

    protected function fail(string $error, int $status = 400): \Illuminate\Http\JsonResponse
    {
        return response()->json(['ok' => false, 'error' => $error, 'data' => []], $status);
    }
}
