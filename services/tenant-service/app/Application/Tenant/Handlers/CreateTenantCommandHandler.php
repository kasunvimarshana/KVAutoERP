<?php

declare(strict_types=1);

namespace App\Application\Tenant\Handlers;

use App\Application\Tenant\Commands\CreateTenantCommand;
use App\Domain\Tenant\Entities\Tenant;
use App\Domain\Tenant\Events\TenantCreated;
use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use App\Domain\Tenant\Services\TenantProvisioningService;
use App\Domain\Tenant\ValueObjects\TenantSlug;
use Illuminate\Support\Facades\Event;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use RuntimeException;

/**
 * Create Tenant Command Handler.
 *
 * Orchestrates tenant creation: persists the record, provisions the database,
 * and fires the TenantCreated domain event to notify downstream services.
 */
final class CreateTenantCommandHandler
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly TenantProvisioningService $provisioningService,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {}

    /**
     * Handle the CreateTenantCommand.
     *
     * @param  CreateTenantCommand  $command
     * @return array<string, mixed>  Newly created tenant data.
     *
     * @throws RuntimeException  When a tenant with the same slug already exists.
     */
    public function handle(CreateTenantCommand $command): array
    {
        // Validate slug uniqueness.
        $slug = new TenantSlug($command->slug);
        $existing = $this->tenantRepository->findBySlug($slug->getValue());

        if ($existing !== null) {
            throw new RuntimeException(
                'A tenant with slug "' . $slug->getValue() . '" already exists.'
            );
        }

        $tenantId = Uuid::uuid4()->toString();

        // Derive the database name before persistence so it is stored immediately.
        $dbName = $this->provisioningService->generateDatabaseName(
            new Tenant(
                id: $tenantId,
                name: $command->name,
                slug: $slug->getValue(),
                domain: $command->domain,
                databaseName: '', // placeholder
                settings: $command->settings,
                isActive: true,
                plan: $command->plan,
                billingEmail: $command->billingEmail,
            )
        );

        // Persist tenant record.
        $tenantData = $this->tenantRepository->create([
            'id'            => $tenantId,
            'name'          => $command->name,
            'slug'          => $slug->getValue(),
            'domain'        => $command->domain,
            'database_name' => $dbName,
            'settings'      => $command->settings,
            'is_active'     => true,
            'plan'          => $command->plan,
            'billing_email' => $command->billingEmail,
        ]);

        $tenant = Tenant::fromArray($tenantData);

        // Provision the isolated database if auto_provision is enabled.
        if (config('tenancy.auto_provision', true)) {
            $this->provisioningService->provision($tenant);
        }

        Event::dispatch(new TenantCreated(
            tenantId: $tenant->getId(),
            name: $tenant->getName(),
            slug: $tenant->getSlug(),
            plan: $tenant->getPlan(),
        ));

        $this->logger->info('[CreateTenantHandler] Tenant created', [
            'tenant_id' => $tenant->getId(),
            'slug'      => $tenant->getSlug(),
        ]);

        return $tenant->toArray();
    }
}
