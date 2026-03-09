<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Services;

use App\Domain\Tenant\Entities\Tenant;
use App\Domain\Tenant\Events\TenantProvisioned;
use App\Domain\Tenant\ValueObjects\DatabaseName;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

/**
 * Tenant Provisioning Domain Service.
 *
 * Orchestrates the lifecycle of a tenant's isolated database:
 *  - Database creation
 *  - Schema migrations
 *  - Seed data
 *  - Default admin user (via event)
 */
final class TenantProvisioningService
{
    public function __construct(
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {}

    /**
     * Provision all infrastructure resources for a newly created tenant.
     *
     * @param  Tenant  $tenant  The tenant entity to provision.
     *
     * @throws RuntimeException  When provisioning fails at any stage.
     */
    public function provision(Tenant $tenant): void
    {
        $this->logger->info('[TenantProvisioning] Starting provisioning', [
            'tenant_id' => $tenant->getId(),
            'slug'      => $tenant->getSlug(),
        ]);

        try {
            $this->createDatabase($tenant);
            $this->configureDynamicConnection($tenant);
            $this->runMigrations($tenant);
            $this->seedInitialData($tenant);

            Event::dispatch(new TenantProvisioned(
                tenantId: $tenant->getId(),
                databaseName: $tenant->getDatabaseName(),
            ));

            $this->logger->info('[TenantProvisioning] Provisioning complete', [
                'tenant_id'     => $tenant->getId(),
                'database_name' => $tenant->getDatabaseName(),
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('[TenantProvisioning] Provisioning failed', [
                'tenant_id' => $tenant->getId(),
                'error'     => $e->getMessage(),
            ]);

            throw new RuntimeException(
                'Failed to provision tenant "' . $tenant->getSlug() . '": ' . $e->getMessage(),
                previous: $e,
            );
        }
    }

    /**
     * Deprovision (remove) all infrastructure resources for a tenant.
     *
     * @param  Tenant  $tenant  The tenant entity to deprovision.
     *
     * @throws RuntimeException  When deprovisioning fails.
     */
    public function deprovision(Tenant $tenant): void
    {
        $this->logger->info('[TenantProvisioning] Starting deprovisioning', [
            'tenant_id' => $tenant->getId(),
        ]);

        try {
            $this->dropDatabase($tenant);

            $this->logger->info('[TenantProvisioning] Deprovisioning complete', [
                'tenant_id' => $tenant->getId(),
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('[TenantProvisioning] Deprovisioning failed', [
                'tenant_id' => $tenant->getId(),
                'error'     => $e->getMessage(),
            ]);

            throw new RuntimeException(
                'Failed to deprovision tenant "' . $tenant->getSlug() . '": ' . $e->getMessage(),
                previous: $e,
            );
        }
    }

    /**
     * Derive the database name for a tenant from the tenancy config.
     */
    public function generateDatabaseName(Tenant $tenant): string
    {
        $prefix = config('tenancy.database_prefix', 'tenant_');
        $suffix = config('tenancy.database_suffix', '');
        $base   = preg_replace('/[^a-zA-Z0-9_]/', '_', $tenant->getSlug()) ?? $tenant->getSlug();

        $name = new DatabaseName($prefix . $base . $suffix);

        return $name->getValue();
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    private function createDatabase(Tenant $tenant): void
    {
        $dbName = $tenant->getDatabaseName();

        /** @var \Illuminate\Database\ConnectionInterface $connection */
        $connection = DB::connection(config('database.default'));

        $escaped = '`' . str_replace('`', '``', $dbName) . '`';
        $connection->statement("CREATE DATABASE IF NOT EXISTS {$escaped} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $this->logger->debug('[TenantProvisioning] Database created', ['db' => $dbName]);
    }

    private function configureDynamicConnection(Tenant $tenant): void
    {
        $connectionName = 'tenant_' . $tenant->getSlug();

        Config::set('database.connections.' . $connectionName, $tenant->getDbConnectionConfig());

        DB::purge($connectionName);
        DB::reconnect($connectionName);
    }

    private function runMigrations(Tenant $tenant): void
    {
        $connectionName = 'tenant_' . $tenant->getSlug();

        Artisan::call('migrate', [
            '--database' => $connectionName,
            '--path'     => 'database/migrations/tenant',
            '--force'    => true,
        ]);

        $this->logger->debug('[TenantProvisioning] Migrations run', [
            'tenant_id' => $tenant->getId(),
        ]);
    }

    private function seedInitialData(Tenant $tenant): void
    {
        $this->logger->debug('[TenantProvisioning] Initial seed skipped (handled by event consumer)', [
            'tenant_id' => $tenant->getId(),
        ]);
    }

    private function dropDatabase(Tenant $tenant): void
    {
        $dbName  = $tenant->getDatabaseName();
        $escaped = '`' . str_replace('`', '``', $dbName) . '`';

        DB::statement("DROP DATABASE IF EXISTS {$escaped}");

        $this->logger->debug('[TenantProvisioning] Database dropped', ['db' => $dbName]);
    }
}
