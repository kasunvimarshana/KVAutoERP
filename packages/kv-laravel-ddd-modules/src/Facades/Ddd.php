<?php

declare(strict_types=1);

namespace LaravelDDD\Facades;

use Illuminate\Support\Facades\Facade;
use LaravelDDD\Contracts\ContextRegistrar;

/**
 * Facade for the ContextRegistrar contract.
 *
 * @method static void   register(string $contextName, string $contextPath)
 * @method static array  all()
 * @method static array|null get(string $contextName)
 * @method static bool   has(string $contextName)
 * @method static void   discover(string $basePath)
 *
 * @see \LaravelDDD\Contracts\ContextRegistrar
 */
class Ddd extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return ContextRegistrar::class;
    }
}
