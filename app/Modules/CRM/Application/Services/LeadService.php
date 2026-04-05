<?php
declare(strict_types=1);
namespace Modules\CRM\Application\Services;
use Modules\CRM\Application\Contracts\LeadServiceInterface;
use Modules\CRM\Domain\Entities\Lead;
use Modules\CRM\Domain\Entities\Opportunity;
use Modules\CRM\Domain\RepositoryInterfaces\LeadRepositoryInterface;
use Modules\CRM\Domain\RepositoryInterfaces\OpportunityRepositoryInterface;
use Illuminate\Support\Collection;
class LeadService implements LeadServiceInterface {
    public function __construct(
        private readonly LeadRepositoryInterface $leadRepository,
        private readonly OpportunityRepositoryInterface $opportunityRepository,
    ) {}
    public function create(array $data): Lead {
        return $this->leadRepository->create($data);
    }
    public function update(int $id, array $data): Lead {
        return $this->leadRepository->update($id, $data);
    }
    public function delete(int $id): bool {
        return $this->leadRepository->delete($id);
    }
    public function findById(int $id): ?Lead {
        return $this->leadRepository->findById($id);
    }
    public function findByTenant(int $tenantId): Collection {
        return $this->leadRepository->findByTenant($tenantId);
    }
    public function convertToOpportunity(int $leadId, array $opportunityData): Opportunity {
        $lead = $this->leadRepository->findById($leadId);
        $data = array_merge([
            'tenant_id'  => $lead->tenantId,
            'contact_id' => $lead->contactId,
            'name'       => $lead->name,
        ], $opportunityData);
        $opportunity = $this->opportunityRepository->create($data);
        $this->leadRepository->update($leadId, ['status' => 'converted']);
        return $opportunity;
    }
}
