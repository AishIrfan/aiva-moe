<?php

namespace App\Observers;

use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    public function created(Model $model): void
    {
        AuditLogger::log(class_basename($model).'.created', $model, [], $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        AuditLogger::log(class_basename($model).'.updated', $model, $model->getOriginal(), $model->getChanges());
    }

    public function deleted(Model $model): void
    {
        AuditLogger::log(class_basename($model).'.deleted', $model, $model->getOriginal(), []);
    }
}
