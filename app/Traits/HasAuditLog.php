<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * Automatic audit logging on model create/update/delete.
 * Uses boot() — attach to any Model by using this trait.
 *
 * Logs are written to the `audit_logs` table (see migration).
 * Fallback to Laravel Log if table doesn't exist yet.
 */
trait HasAuditLog
{
    protected static function bootHasAuditLog(): void
    {
        static::created(function ($model) {
            static::writeAudit('created', $model, [], $model->getAttributes());
        });

        static::updated(function ($model) {
            static::writeAudit('updated', $model, $model->getOriginal(), $model->getChanges());
        });

        static::deleted(function ($model) {
            static::writeAudit('deleted', $model, $model->getAttributes(), []);
        });
    }

    protected static function writeAudit(string $event, $model, array $before, array $after): void
    {
        try {
            \App\Models\AuditLog::create([
                'user_id'    => Auth::id(),
                'event'      => $event,
                'auditable_type' => get_class($model),
                'auditable_id'   => $model->getKey(),
                'before'     => !empty($before) ? json_encode($before) : null,
                'after'      => !empty($after)  ? json_encode($after)  : null,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Fallback: log to file if audit_logs table not yet available
            \Illuminate\Support\Facades\Log::channel('daily')->warning(
                "[AuditLog] {$event} on " . get_class($model) . " #{$model->getKey()}: " . $e->getMessage()
            );
        }
    }
}
