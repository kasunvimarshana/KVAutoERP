<?php

declare(strict_types=1);

namespace App\Application\Tenant\Handlers;

use App\Application\Tenant\Commands\UpdateTenantCommand;
use App\Domain\Tenant\Entities\Tenant;
use App\Domain\Tenant\Events\TenantUpdated;
use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

/**
 * Update Tenant Command Handler.
 *
 * Applies partial updates to an existing tenant and fires TenantUpdated.
 */
final class UpdateTenantCommandHandler
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {}

    /**
     * Handle the UpdateTenantCommand.
     *
     * @param  UpdateTenantCommand  $command
     * @return array<string, mixed>  Updated tenant data.
     *
     * @throws ModelNotFoundException  When the tenant does not exist.
     */
    public function handle(UpdateTenantCommand $command): array
    {
        $existing = $this->tenantRepository->findById($command->tenantId);

        if ($existing === null) {
            throw new ModelNotFoundException(
                'Tenant not found: ' . $command->tenantId
            );
        }

        // Build the delta — only include non-null fields from the command.
        $changes = [];

        if ($command->name !== null) {
            $changes['name'] = $command->name;
        }

        if ($command->domain !== null) {
            $changes['domain'] = $command->domain === '' ? null : $command->domain;
        }

        if ($command->plan !== null) {
            $changes['plan'] = $command->plan;
        }

        if ($command->billingEmail !== null) {
            $changes['billing_email'] = $command->billingEmail;
        }

        if ($command->isActive !== null) {
            $changes['is_active'] = $command->isActive;
        }

        if ($command->settings !== null) {
            // Merge settings instead of replacing to allow partial updates.
            $changes['settings'] = array_merge(
                is_array($existing['settings'] ?? null) ? $existing['settings'] : [],
                $command->settings,
            );
        }

        if (empty($changes)) {
            return $existing;
        }

        $updated = $this->tenantRepository->update($command->tenantId, $changes);
        $tenant  = Tenant::fromArray($updated);

        Event::dispatch(new TenantUpdated(
            tenantId: $tenant->getId(),
            changedFields: $changes,
        ));

        $this->logger->info('[UpdateTenantHandler] Tenant updated', [
            'tenant_id' => $tenant->getId(),
            'fields'    => array_keys($changes),
        ]);

        return $tenant->toArray();
    }
}
