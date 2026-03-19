<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Traits;

use Illuminate\Database\Eloquent\Model;
use KvEnterprise\SharedKernel\Exceptions\OptimisticLockException;

/**
 * Implements optimistic locking via a `version` (integer) column.
 *
 * On every `save()`, the trait:
 *   1. Adds a `WHERE version = <current>` predicate to the UPDATE.
 *   2. Increments `version` in the same UPDATE statement.
 *   3. Throws an {@see OptimisticLockException} when no row was affected
 *      (meaning another process already incremented the version).
 *
 * Requirements for the host model:
 *   - Table must have a `version` column (unsigned integer, default 0).
 *   - The `version` column should NOT be in `$hidden`.
 *
 * Behaviour when no attributes are dirty:
 *   Calling save() on an unchanged existing model will still issue the
 *   UPDATE to increment the version column. This is a deliberate safety
 *   measure: it ensures that any concurrent save that occurred between
 *   the model being loaded and save() being called will be detected.
 *   The version column is always incremented as part of every save().
 *
 * Limitation with HasAuditLog:
 *   The HasAuditLog trait's deleting listener uses a direct table UPDATE
 *   that bypasses this version check. When both traits are used together,
 *   ensure the service layer guards the version before calling delete().
 *
 * Usage:
 *   $model->name = 'new name';
 *   $model->save(); // throws OptimisticLockException if version was already incremented elsewhere
 */
trait HasOptimisticLocking
{
    /**
     * Boot the HasOptimisticLocking trait.
     *
     * Initialises the version column to 0 for new model instances.
     *
     * @return void
     */
    public static function bootHasOptimisticLocking(): void
    {
        static::creating(static function (Model $model): void {
            if ($model->version === null) {
                $model->version = 0;
            }
        });
    }

    /**
     * Override the default Eloquent save() with optimistic locking logic.
     *
     * For new records the standard insert path is followed (no version check).
     * For existing records a `UPDATE … WHERE version = ?` is performed.
     * The version column is ALWAYS included in the update payload regardless
     * of whether other attributes are dirty, ensuring the conflict check fires
     * consistently on every save() call.
     *
     * @param  array<string, mixed>  $options  Options forwarded to Eloquent's event pipeline.
     * @return bool                             True on success.
     *
     * @throws OptimisticLockException When a concurrent modification is detected.
     */
    public function save(array $options = []): bool
    {
        if (!$this->exists) {
            return parent::save($options);
        }

        $currentVersion = (int) $this->getOriginal('version', $this->version ?? 0);
        $nextVersion    = $currentVersion + 1;

        // Always include dirty attributes plus the version increment.
        // This guarantees the conflict check fires even on no-op saves.
        $payload = array_merge($this->getDirty(), ['version' => $nextVersion]);

        $affected = $this->getConnection()
            ->table($this->getTable())
            ->where($this->getKeyName(), $this->getKey())
            ->where('version', $currentVersion)
            ->update($payload);

        if ($affected === 0) {
            throw OptimisticLockException::forModel(static::class, $this->getKey(), $currentVersion);
        }

        // Sync the in-memory model state to reflect the committed values.
        $this->version = $nextVersion;
        $this->syncOriginal();

        $this->fireModelEvent('saved', false);

        return true;
    }

    /**
     * Return the current version number of this model instance.
     *
     * @return int
     */
    public function getVersion(): int
    {
        return (int) ($this->getAttribute('version') ?? 0);
    }
}
