<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        // Log Created Event
        static::created(function ($model) {
            if (self::shouldLog($model, 'created')) {
                self::logActivity($model, 'created', 'Created new ' . class_basename($model));
            }
        });

        // Log Updated Event
        static::updated(function ($model) {
            if ($model->isDirty() && self::shouldLog($model, 'updated')) {
                $changes = $model->getChanges();
                $original = $model->getOriginal();
                
                unset($changes['updated_at']);
                unset($original['updated_at']);

                if (!empty($changes)) {
                    self::logActivity($model, 'updated', 'Updated ' . class_basename($model), [
                        'attributes' => $changes,
                        'old' => array_intersect_key($original, $changes)
                    ]);
                }
            }
        });

        // Log Deleted Event
        static::deleted(function ($model) {
            if (self::shouldLog($model, 'deleted')) {
                self::logActivity($model, 'deleted', 'Deleted ' . class_basename($model));
            }
        });
    }

    protected static function shouldLog($model, $event)
    {
        if (isset($model->logEvents)) {
            return in_array($event, $model->logEvents);
        }
        return true;
    }

    protected static function logActivity($model, $event, $description, $properties = [])
    {
        // Don't log if no user is authenticated (e.g. seeder) unless critical? 
        // We generally only want to track USER actions.
        if (Auth::check()) {
            if (request()->has('reason')) {
                $properties['reason'] = request('reason');
            }
            ActivityLog::create([
                'causer_id' => Auth::id(),
                'subject_type' => get_class($model),
                'subject_id' => $model->id,
                'description' => $description,
                'properties' => $properties,
                'ip_address' => request()->ip()
            ]);
        }
    }
}
