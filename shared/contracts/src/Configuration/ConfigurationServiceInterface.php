<?php

declare(strict_types=1);

namespace KvSaas\Contracts\Configuration;

/**
 * Runtime tenant configuration service contract.
 * Used by every microservice to read tenant-specific settings dynamically.
 */
interface ConfigurationServiceInterface
{
    public function get(string $tenantId, string $key, mixed $default = null): mixed;

    public function set(string $tenantId, string $key, mixed $value): void;

    /** @return array<string, mixed> */
    public function getAll(string $tenantId): array;

    public function getFeatureFlag(string $tenantId, string $flag): bool;

    /** @return array<string, mixed> */
    public function getIamProviderConfig(string $tenantId): array;
}
