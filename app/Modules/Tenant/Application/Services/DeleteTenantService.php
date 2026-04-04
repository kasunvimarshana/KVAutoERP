<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Tenant\Application\Contracts\DeleteTenantServiceInterface;
use Modules\Tenant\Domain\Events\TenantDeleted;
use Modules\Tenant\Domain\Exceptions\TenantNotFoundException;
use Modules\Tenant\Domain\Repositories\TenantRepositoryInterface;

class DeleteTenantService implements DeleteTenantServiceInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $repository,
    ) {}

    public function execute(int $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            if ($this->repository->findById($id) === null) {
                throw new TenantNotFoundException($id);
            }

            $result = $this->repository->delete($id);

            if ($result) {
                Event::dispatch(new TenantDeleted($id));
            }

            return $result;
        });
    }
}
