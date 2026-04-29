<?php

/**
 * IPG-mode application config.
 *
 * Establishes the configuration surface for IPG-wide values that don't warrant
 * a database settings table (per Wave 1 locked decision #10 — "promote when a
 * second IPG-wide config value emerges"). Wave 2 introduces uploads (the second
 * value), so the file is created here and `pensyarah.max_trainee_load` is
 * promoted from a hardcoded constant at the same time.
 *
 * Add new IPG-wide config keys here rather than scattering constants across
 * model classes.
 */
return [

    'leave' => [
        /*
        |----------------------------------------------------------------------
        | Pensyarah response threshold (days)
        |----------------------------------------------------------------------
        |
        | Per IPG_WORKFLOWS.md §W1.4 edge case: "Pensyarah doesn't respond in
        | time: after configurable threshold, treated as `Acknowledged` (silent
        | acceptance)." This sets that window. The deadline is snapshotted onto
        | each request's `response_threshold_at` at submission time so it can
        | be overridden per request without changing this default.
        |
        */
        'response_threshold_days' => (int) env('IPG_LEAVE_RESPONSE_THRESHOLD_DAYS', 7),
    ],

    'attendance' => [
        /*
        |----------------------------------------------------------------------
        | Late-edit threshold (days)
        |----------------------------------------------------------------------
        |
        | After a Pensyarah submits attendance for a session (`recorded_at`
        | set), edits remain allowed for this many days. Beyond the threshold,
        | the session's `locked_at` is set and further edits require IPG Admin
        | unlock with a recorded reason. Per IPG_WORKFLOWS.md §W1.1 default 3.
        |
        */
        'late_edit_threshold_days' => (int) env('IPG_ATTENDANCE_LATE_EDIT_DAYS', 3),
    ],

    'pensyarah' => [
        /*
        |----------------------------------------------------------------------
        | Maximum trainees per Pensyarah supervisor
        |----------------------------------------------------------------------
        |
        | Soft cap enforced by the Penyelaras Praktikum supervisor-assignment
        | workflow (W3.3). Overridable per-assignment with a recorded reason.
        | Promoted from `Pensyarah::MAX_TRAINEE_LOAD` in Wave 2 Unit A.
        |
        */
        'max_trainee_load' => (int) env('IPG_PENSYARAH_MAX_TRAINEE_LOAD', 8),
    ],

    'uploads' => [

        'course_materials' => [
            /*
            |------------------------------------------------------------------
            | Storage disk for course materials
            |------------------------------------------------------------------
            |
            | Defaults to Laravel's `local` disk (rooted at storage/app/private
            | in Laravel 11+) — i.e. NOT the public web root. Course materials
            | are scoped to authenticated users enrolled in the offering and
            | must be served via an auth-gated download controller, never via
            | a direct public URL. Override to `s3` (or another configured
            | disk) when remote storage is wired up.
            |
            */
            'disk' => env('IPG_COURSE_MATERIALS_DISK', 'local'),

            /*
            |------------------------------------------------------------------
            | Maximum file size (megabytes)
            |------------------------------------------------------------------
            |
            | Per-file cap enforced at the validation layer (NOT only via
            | nginx/PHP `upload_max_filesize`). 50 MB matches IPG_WORKFLOWS.md
            | §W1.7.1 "sane default".
            |
            */
            'max_file_size_mb' => (int) env('IPG_COURSE_MATERIALS_MAX_MB', 50),

            /*
            |------------------------------------------------------------------
            | Allowed MIME types
            |------------------------------------------------------------------
            |
            | Sniffed at upload time (NOT inferred from extension). Trim or
            | extend per institutional policy. PDFs, Office docs, common image
            | and AV formats, plus ZIP for grouped resources.
            |
            */
            'allowed_mime_types' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'image/png',
                'image/jpeg',
                'image/gif',
                'video/mp4',
                'audio/mpeg',
                'application/zip',
            ],
        ],

    ],

];
