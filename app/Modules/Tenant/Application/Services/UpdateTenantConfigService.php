<?php

namespace Modules\Tenant\Application\Services;

use Modules\Core\Application\Services\BaseService;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use Modules\Tenant\Application\DTOs\TenantConfigData;
use Modules\Tenant\Domain\Events\TenantConfigChanged;

class UpdateTenantConfigService extends BaseService
{
    public function __construct(TenantRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function handle(array $data): \Modules\Tenant\Domain\Entities\Tenant
    {
        $id = $data['id'];
        $dto = TenantConfigData::fromArray($data);

        $tenant = $this->repository->find($id);
        if (!$tenant) {
            throw new \RuntimeException('Tenant not found');
        }

        $tenant->updateConfig($dto->toArray());
        $saved = $this->repository->save($tenant);
        $this->addEvent(new TenantConfigChanged($saved));
        return $saved;
    }
}
