<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActivityLogger
{
    protected ?string $logName = 'default';
    protected ?Model $subject = null;
    protected ?int $userId = null;
    protected array $properties = [];

    public function __construct(?string $logName = 'default')
    {
        $this->logName = $logName ?: 'default';
        $this->resolveUserId();
    }

    public static function use(?string $logName = 'default'): self
    {
        return new static($logName);
    }

    public function inLog(string $logName): self
    {
        $this->logName = $logName;
        return $this;
    }

    public function performedOn(?Model $subject): self
    {
        $this->subject = $subject;

        if (!$this->userId && $subject && isset($subject->user_id) && is_numeric($subject->user_id)) {
            $this->userId = (int) $subject->user_id;
        }

        return $this;
    }

    public function causedBy($user): self
    {
        if ($user instanceof User) {
            $this->userId = (int) $user->id;
        } elseif (is_numeric($user)) {
            $this->userId = (int) $user;
        }
        return $this;
    }

    public function withProperties(array $properties): self
    {
        $this->properties = array_merge($this->properties, $properties);
        return $this;
    }

    protected function resolveUserId(): void
    {
        try {
            $id = Auth::id()
                ?? Auth::guard('sanctum')->id()
                ?? request()?->user()?->id;

            if ($id && is_numeric($id)) {
                $this->userId = (int) $id;
            }
        } catch (Throwable $e) {
            // Ignore auth resolution errors
        }
    }

    public function log(string $description): ?ActivityLog
    {
        try {
            $request = request();

            // Final fallback to subject's user_id if available
            if (!$this->userId && $this->subject && isset($this->subject->user_id) && is_numeric($this->subject->user_id)) {
                $this->userId = (int) $this->subject->user_id;
            }

            return ActivityLog::create([
                'log_name'     => $this->logName ?: 'default',
                'description'  => $description,
                'user_id'      => $this->userId,
                'subject_id'   => $this->subject ? $this->subject->getKey() : null,
                'subject_type' => $this->subject ? get_class($this->subject) : null,
                'properties'   => !empty($this->properties) ? $this->properties : null,
                'ip_address'   => $request ? $request->ip() : null,
                'user_agent'   => $request ? substr($request->userAgent() ?? '', 0, 500) : null,
            ]);
        } catch (Throwable $e) {
            Log::error("[ActivityLogger] Failed to insert ActivityLog: " . $e->getMessage(), [
                'description' => $description,
                'exception'   => $e
            ]);
            return null;
        }
    }
}
