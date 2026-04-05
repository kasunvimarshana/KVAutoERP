<?php
declare(strict_types=1);
namespace Modules\CRM\Application\Contracts;
use Modules\CRM\Domain\Entities\Lead;
use Modules\CRM\Domain\Entities\Opportunity;
use Illuminate\Support\Collection;
interface LeadServiceInterface {
    public function create(array $data): Lead;
    public function update(int $id, array $data): Lead;
    public function delete(int $id): bool;
    public function findById(int $id): ?Lead;
    public function findByTenant(int $tenantId): Collection;
    public function convertToOpportunity(int $leadId, array $opportunityData): Opportunity;
}
