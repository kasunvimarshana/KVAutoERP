<?php

declare(strict_types=1);

namespace App\Application\Tenant\Handlers;

use App\Application\Tenant\Commands\UpdateTenantConfigCommand;
use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use App\Infrastructure\Runtime\RuntimeConfigManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Update Tenant Config Command Handler.
 *
 * Persists a single configuration key for a tenant and applies it to the
 * runtime config if the tenant is the currently active context.
 */
final class UpdateTenantConfigCommandHandler
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly RuntimeConfigManager $runtimeConfig,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {}

    /**
     * Handle the UpdateTenantConfigCommand.
     *
     * @param  UpdateTenantConfigCommand  $command
     *
     * @throws ModelNotFoundException  When the tenant does not exist.
     */
    public function handle(UpdateTenantConfigCommand $command): void
    {
        $existing = $this->tenantRepository->findById($command->tenantId);

        if ($existing === null) {
            throw new ModelNotFoundException(
                'Tenant not found: ' . $command->tenantId
            );
        }

        $this->tenantRepository->updateConfiguration(
            $command->tenantId,
            $command->configKey,
            $command->configValue,
        );

        // Apply the change to the running application config for the active request.
        $this->runtimeConfig->applyConfig([
            $command->configKey => $command->configValue,
        ]);

        $this->logger->info('[UpdateTenantConfigHandler] Config updated', [
            'tenant_id'  => $command->tenantId,
            'config_key' => $command->configKey,
            'environment' => $command->environment,
        ]);
    }
}
