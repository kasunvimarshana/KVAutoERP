<?php

declare(strict_types=1);

namespace App\Application\Tenant\Handlers;

use App\Application\Tenant\Commands\DeleteTenantCommand;
use App\Domain\Tenant\Events\TenantDeleted;
use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Delete Tenant Command Handler.
 *
 * Soft-deletes a tenant and fires the TenantDeleted domain event.
 * Actual database deprovisioning is handled asynchronously by an event consumer.
 */
final class DeleteTenantCommandHandler
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {}

    /**
     * Handle the DeleteTenantCommand.
     *
     * @param  DeleteTenantCommand  $command
     *
     * @throws ModelNotFoundException  When the tenant does not exist.
     */
    public function handle(DeleteTenantCommand $command): void
    {
        $existing = $this->tenantRepository->findById($command->tenantId);

        if ($existing === null) {
            throw new ModelNotFoundException(
                'Tenant not found: ' . $command->tenantId
            );
        }

        $this->tenantRepository->delete($command->tenantId);

        Event::dispatch(new TenantDeleted(tenantId: $command->tenantId));

        $this->logger->info('[DeleteTenantHandler] Tenant deleted', [
            'tenant_id'    => $command->tenantId,
            'performed_by' => $command->performedBy,
        ]);
    }
}
