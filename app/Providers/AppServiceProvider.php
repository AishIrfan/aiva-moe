<?php

namespace App\Providers;

use App\Models\DisciplineCase;
use App\Models\Event;
use App\Models\LeaveSubmission;
use App\Models\ManagementEvent;
use App\Observers\AuditableObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Audit observer on workflow-bearing models (creation / update / deletion → audit_logs).
        foreach ([Event::class, DisciplineCase::class, LeaveSubmission::class, ManagementEvent::class] as $model) {
            $model::observe(AuditableObserver::class);
        }
    }
}
