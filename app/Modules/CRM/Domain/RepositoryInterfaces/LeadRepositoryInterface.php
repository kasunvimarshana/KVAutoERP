<?php declare(strict_types=1);
namespace Modules\CRM\Domain\RepositoryInterfaces;
use Modules\CRM\Domain\Entities\Lead;
interface LeadRepositoryInterface {
    public function findById(int $id): ?Lead;
    public function findByTenant(int $tenantId, ?string $status = null): array;
    public function save(Lead $lead): Lead;
    public function delete(int $id): void;
}
