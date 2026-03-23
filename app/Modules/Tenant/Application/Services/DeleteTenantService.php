<?php

namespace Modules\Tenant\Application\Services;

use Modules\Core\Application\Services\BaseService;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use Modules\Tenant\Domain\Events\TenantDeleted;

class DeleteTenantService extends BaseService
{
    public function __construct(TenantRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function handle(array $data): bool
    {
        $id = $data['id'];
        $tenant = $this->repository->find($id);
        if (!$tenant) {
            throw new \RuntimeException('Tenant not found');
        }
        $deleted = $this->repository->delete($id);
        if ($deleted) {
            $this->addEvent(new TenantDeleted($id));
        }
        return $deleted;
    }
}
