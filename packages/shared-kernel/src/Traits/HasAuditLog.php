<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Automatically captures `created_by`, `updated_by`, and `deleted_by`
 * from the authenticated user on every write operation.
 *
 * Requirements for the host model:
 *   - Table must have `created_by` (nullable UUID), `updated_by` (nullable UUID),
 *     and `deleted_by` (nullable UUID, only when soft-deletes are used) columns.
 *
 * The audit columns are populated from the authenticated user's ID.
 * When no user is authenticated (e.g., console commands or system jobs),
 * the columns are left as-is to avoid overwriting a previously set value.
 */
trait HasAuditLog
{
    /**
     * Boot the HasAuditLog trait and attach model event listeners.
     *
     * Called automatically by Eloquent when the host model is booted.
     *
     * @return void
     */
    public static function bootHasAuditLog(): void
    {
        static::creating(static function (Model $model): void {
            $userId = static::resolveAuthenticatedUserId();

            if ($userId !== null) {
                if (static::modelHasColumn($model, 'created_by') && empty($model->created_by)) {
                    $model->created_by = $userId;
                }

                if (static::modelHasColumn($model, 'updated_by') && empty($model->updated_by)) {
                    $model->updated_by = $userId;
                }
            }
        });

        static::updating(static function (Model $model): void {
            $userId = static::resolveAuthenticatedUserId();

            if ($userId !== null && static::modelHasColumn($model, 'updated_by')) {
                $model->updated_by = $userId;
            }
        });

        // Only attach if the host model uses SoftDeletes.
        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(static::class), true)) {
            static::deleting(static function (Model $model): void {
                $userId = static::resolveAuthenticatedUserId();

                if ($userId !== null && static::modelHasColumn($model, 'deleted_by')) {
                    // NOTE: This direct table update bypasses Eloquent's normal model pipeline,
                    // including optimistic locking version checks (HasOptimisticLocking trait).
                    // It is intentional — we must persist deleted_by before the soft-delete
                    // timestamp is written. If the model also uses HasOptimisticLocking, ensure
                    // the version column is guarded at the service layer before calling delete().
                    $model->getConnection()
                        ->table($model->getTable())
                        ->where($model->getKeyName(), $model->getKey())
                        ->update(['deleted_by' => $userId]);
                }
            });
        }
    }

    /**
     * Resolve the current authenticated user's identifier.
     *
     * @return string|int|null  UUID / primary key of the authenticated user, or null.
     */
    private static function resolveAuthenticatedUserId(): string|int|null
    {
        try {
            return Auth::id();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Determine whether the model's table has a given column in its fillable / visible set.
     *
     * We use a try/catch to gracefully degrade when the schema is not yet migrated
     * (e.g., during early bootstrapping or test environments).
     *
     * @param  Model   $model   The Eloquent model instance.
     * @param  string  $column  Column name to check.
     * @return bool
     */
    private static function modelHasColumn(Model $model, string $column): bool
    {
        try {
            return $model->getConnection()
                ->getSchemaBuilder()
                ->hasColumn($model->getTable(), $column);
        } catch (\Throwable) {
            return false;
        }
    }
}
