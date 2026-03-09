<?php

declare(strict_types=1);

namespace App\Core\Abstracts\Services;

use App\Core\Contracts\Services\ServiceInterface;

/**
 * BaseService
 *
 * Abstract foundation for all application services.
 * Provides a clean extension point for cross-cutting concerns
 * (logging, event publishing, etc.) without modifying individual services.
 */
abstract class BaseService implements ServiceInterface
{
    // Cross-cutting concerns (logging, event dispatch, etc.) can be
    // injected and made available to all services through this base class.
}
