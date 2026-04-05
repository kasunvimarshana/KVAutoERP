<?php
declare(strict_types=1);
namespace Modules\CRM\Application\Contracts;
use Modules\CRM\Domain\Entities\Activity;
use Illuminate\Support\Collection;
interface ActivityServiceInterface {
    public function create(array $data): Activity;
    public function update(int $id, array $data): Activity;
    public function delete(int $id): bool;
    public function findById(int $id): ?Activity;
    public function findByTenant(int $tenantId): Collection;
    public function complete(int $id): Activity;
    public function getForContact(int $contactId): Collection;
    public function getForOpportunity(int $opportunityId): Collection;
}
