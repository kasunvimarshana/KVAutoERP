<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Contracts\Service;

/**
 * Marker interface for all application service classes.
 *
 * Provides a common type for dependency injection bindings and
 * enables service-level middleware (logging, tracing, etc.) to be
 * applied uniformly across all services in the platform.
 */
interface ServiceInterface
{
    // Intentionally empty – acts as a type marker.
    // Concrete services define their own domain-specific method contracts.
}
