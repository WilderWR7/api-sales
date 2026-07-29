<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_log';

    protected $fillable = [
        'log_name',
        'description',
        'user_id',
        'subject_id',
        'subject_type',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Get the user who performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subject model of the activity (Product, Sale, etc.).
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter logs by channel/log_name.
     */
    public function scopeInLog(Builder $query, string $logName): Builder
    {
        return $query->where('log_name', $logName);
    }

    /**
     * Scope to filter logs by user.
     */
    public function scopeCausedBy(Builder $query, $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter logs by subject.
     */
    public function scopeForSubject(Builder $query, Model $subject): Builder
    {
        return $query->where('subject_type', get_class($subject))
            ->where('subject_id', $subject->getKey());
    }
}
