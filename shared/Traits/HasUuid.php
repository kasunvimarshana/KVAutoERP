<?php

declare(strict_types=1);

namespace Shared\Traits;

use Illuminate\Support\Str;

/**
 * HasUuid Trait
 * 
 * Automatically generates a UUID for models that use string primary keys.
 * Also adds UUID-based finder methods.
 */
trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Indicate that the model's ID is not auto-incrementing.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Get the auto-incrementing ID type (string for UUIDs).
     */
    public function getKeyType(): string
    {
        return 'string';
    }
}
