<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

// Public ingestion endpoints (secured by upstream token, not session)
Route::post('/fr/trigger', [Api\FrEventTriggerController::class, 'store']);
Route::post('/bat/trigger', [Api\BatEventTriggerController::class, 'store']);

Route::get('/fr/events', [Api\FrEventController::class, 'index']);
Route::get('/bat/events', [Api\BatEventController::class, 'index']);

// CRUD wrappers mirroring js/lib/api.js — session-auth via web guard
Route::middleware(['web', 'auth'])->group(function () {
    Route::apiResource('students', Api\StudentController::class);
    Route::apiResource('schools', Api\SchoolController::class);
    Route::apiResource('grades', Api\GradeController::class);
    Route::apiResource('classes', Api\ClassController::class);
    Route::apiResource('users', Api\UserController::class);
    Route::apiResource('absent-reasons', Api\AbsentReasonController::class);
    Route::apiResource('schedules', Api\ScheduleController::class);
    Route::apiResource('aiva-events', Api\AivaEventController::class);
});
