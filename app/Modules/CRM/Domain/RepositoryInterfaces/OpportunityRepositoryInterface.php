<?php

declare(strict_types=1);

namespace Modules\CRM\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\CRM\Domain\Entities\Opportunity;

interface OpportunityRepositoryInterface
{
    public function findById(int $id): ?Opportunity;
    public function findByTenant(int $tenantId): Collection;
    public function findByContact(int $contactId): Collection;
    public function findByStage(int $tenantId, string $stage): Collection;
    public function save(array $data): Opportunity;
    public function update(int $id, array $data): Opportunity;
    public function delete(int $id): void;
}
