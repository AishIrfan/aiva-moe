<?php

/**
 * Class Recording config — separate from `config/ipg.php` since this is a
 * School-mode feature (CLASS_RECORDING_CHECKLIST.md §3 says "do NOT create
 * a new top-level settings infrastructure", referring to per-school
 * key-value settings; this file is global app config, not per-school).
 *
 * Per-school enable/audio/retention/max-size live on the `settings` table
 * via the keys defined as constants on `App\Models\ClassRecording`.
 */
return [

    'smartboard' => [
        /*
        |----------------------------------------------------------------------
        | Smartboard upload bearer token
        |----------------------------------------------------------------------
        |
        | SCAFFOLDING per §5.2 — real smartboard hardware integration is out
        | of scope for v1. The /api/v1/class-recordings/smartboard-upload
        | endpoint exists so external integrators have a target. Token is a
        | shared secret for v1; swap for Sanctum / mTLS / device certs before
        | real hardware ships.
        |
        | Empty / null disables the endpoint entirely (returns 401 to ALL
        | callers — fail closed). Set IPG_SMARTBOARD_TOKEN in .env to enable.
        |
        */
        'token' => env('IPG_SMARTBOARD_TOKEN'),
    ],

];
