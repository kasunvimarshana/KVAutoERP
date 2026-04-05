<?php

declare(strict_types=1);

namespace Modules\CRM\Application\Services;

use Modules\CRM\Application\Contracts\OpportunityServiceInterface;
use Modules\CRM\Domain\Entities\Opportunity;
use Modules\CRM\Domain\RepositoryInterfaces\OpportunityRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

class OpportunityService implements OpportunityServiceInterface
{
    private const STAGE_ORDER = [
        'prospecting',
        'qualification',
        'proposal',
        'negotiation',
        'closed_won',
    ];

    public function __construct(
        private readonly OpportunityRepositoryInterface $repository,
    ) {}

    public function create(array $data): Opportunity
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Opportunity
    {
        $opportunity = $this->repository->update($id, $data);

        if ($opportunity === null) {
            throw new NotFoundException('Opportunity', $id);
        }

        return $opportunity;
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function find(int $id): Opportunity
    {
        $opportunity = $this->repository->findById($id);

        if ($opportunity === null) {
            throw new NotFoundException('Opportunity', $id);
        }

        return $opportunity;
    }

    public function advanceStage(int $id): Opportunity
    {
        $opportunity = $this->find($id);
        $currentIndex = array_search($opportunity->getStage(), self::STAGE_ORDER, true);

        $nextStage = $currentIndex !== false && isset(self::STAGE_ORDER[$currentIndex + 1])
            ? self::STAGE_ORDER[$currentIndex + 1]
            : $opportunity->getStage();

        return $this->update($id, ['stage' => $nextStage]);
    }

    public function closeWon(int $id): Opportunity
    {
        return $this->update($id, ['stage' => 'closed_won', 'probability' => 100]);
    }

    public function closeLost(int $id): Opportunity
    {
        return $this->update($id, ['stage' => 'closed_lost', 'probability' => 0]);
    }
}
