<?php
declare(strict_types=1);
namespace Modules\CRM\Application\Contracts;
use Modules\CRM\Domain\Entities\Opportunity;
use Illuminate\Support\Collection;
interface OpportunityServiceInterface {
    public function create(array $data): Opportunity;
    public function update(int $id, array $data): Opportunity;
    public function delete(int $id): bool;
    public function findById(int $id): ?Opportunity;
    public function findByTenant(int $tenantId): Collection;
    public function updateStage(int $id, string $stage): Opportunity;
    public function markWon(int $id): Opportunity;
    public function markLost(int $id, string $reason): Opportunity;
}
