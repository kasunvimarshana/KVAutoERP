<?php

declare(strict_types=1);

namespace Modules\CRM\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\CRM\Domain\Entities\Activity;

interface ActivityRepositoryInterface
{
    public function findById(int $id): ?Activity;
    public function findByContact(int $contactId): Collection;
    public function findByOpportunity(int $opportunityId): Collection;
    public function findByLead(int $leadId): Collection;
    public function save(array $data): Activity;
    public function update(int $id, array $data): Activity;
    public function delete(int $id): void;
}
