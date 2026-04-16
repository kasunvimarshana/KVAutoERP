<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Illuminate\Support\Collection;
use Modules\Tenant\Application\Contracts\FindTenantPlansServiceInterface;
use Modules\Tenant\Domain\Entities\TenantPlan;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantPlanRepositoryInterface;

class FindTenantPlansService implements FindTenantPlansServiceInterface
{
    public function __construct(
        private readonly TenantPlanRepositoryInterface $planRepository
    ) {}

    public function find(int $id): ?TenantPlan
    {
        return $this->planRepository->find($id);
    }

    public function findBySlug(string $slug): ?TenantPlan
    {
        return $this->planRepository->findBySlug($slug);
    }

    public function listActive(?string $billingInterval = null): Collection
    {
        return $this->planRepository->getActive($billingInterval);
    }
}
