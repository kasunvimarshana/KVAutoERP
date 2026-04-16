<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Tenant\Domain\Entities\TenantPlan;

interface FindTenantPlansServiceInterface
{
    public function find(int $id): ?TenantPlan;

    public function findBySlug(string $slug): ?TenantPlan;

    public function listActive(?string $billingInterval = null): Collection;
}
