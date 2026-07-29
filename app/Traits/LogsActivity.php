<?php

namespace App\Traits;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    /**
     * Boot the trait to record Eloquent model events.
     */
    public static function bootLogsActivity(): void
    {
        static::created(function (Model $model) {
            if ($model->shouldLogEvent('created')) {
                $logName = $model->getActivityLogName();
                $attributes = $model->getLoggableAttributes($model->getAttributes());

                ActivityLogger::use($logName)
                    ->performedOn($model)
                    ->withProperties(['attributes' => $attributes])
                    ->log("Created " . class_basename($model) . " #{$model->getKey()}");
            }
        });

        static::updated(function (Model $model) {
            // Ignore update logging if the change is a soft-delete
            if ($model->isDirty('deleted_at') && !is_null($model->getAttribute('deleted_at'))) {
                return;
            }

            if ($model->shouldLogEvent('updated')) {
                $dirty = $model->getDirty();
                
                // Don't log if no relevant attributes changed
                $changedAttributes = $model->getLoggableAttributes($dirty);
                if (empty($changedAttributes)) {
                    return;
                }

                $oldAttributes = $model->getLoggableAttributes(
                    array_intersect_key($model->getOriginal(), $changedAttributes)
                );

                $logName = $model->getActivityLogName();

                ActivityLogger::use($logName)
                    ->performedOn($model)
                    ->withProperties([
                        'old' => $oldAttributes,
                        'attributes' => $changedAttributes,
                    ])
                    ->log("Updated " . class_basename($model) . " #{$model->getKey()}");
            }
        });

        // Use the 'deleting' event because it always runs right before you hide or permanently remove a record.
        static::deleting(function (Model $model) {
            if ($model->shouldLogEvent('deleted')) {
                $logName = $model->getActivityLogName();
                $attributes = $model->getLoggableAttributes($model->getAttributes());

                ActivityLogger::use($logName)
                    ->performedOn($model)
                    ->withProperties(['attributes' => $attributes])
                    ->log("Deleted " . class_basename($model) . " #{$model->getKey()}");
            }
        });
    }

    /**
     * Get the log channel name for the model.
     */
    public function getActivityLogName(): string
    {
        return property_exists($this, 'logName') && !empty($this->logName)
            ? $this->logName
            : strtolower(class_basename($this));
    }

    /**
     * Determine if a given event should be logged.
     * 
     * @param string $event name of the event
     * @return bool true if the event should be logged, false otherwise
     */
    public function shouldLogEvent(string $event): bool
    {
        $events = property_exists($this, 'logEvents') ? $this->logEvents : ['created', 'updated', 'deleted'];
        return in_array($event, $events);
    }

    /**
     * Filter attributes to log based on $logAttributes property if defined.
     * 
     * @param array $attributes array of attributes to filter
     * @return array filtered attributes
     */
    public function getLoggableAttributes(array $attributes): array
    {
        // Exclude sensitive timestamps and hidden attributes if needed
        unset($attributes['remember_token'], $attributes['password']);

        if (property_exists($this, 'logAttributes') && is_array($this->logAttributes) && !empty($this->logAttributes)) {
            return array_intersect_key($attributes, array_flip($this->logAttributes));
        }

        return $attributes;
    }
}
