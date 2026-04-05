<?php

declare(strict_types=1);

namespace Modules\CRM\Application\Services;

use Modules\CRM\Application\Contracts\LeadServiceInterface;
use Modules\CRM\Domain\Entities\Lead;
use Modules\CRM\Domain\RepositoryInterfaces\LeadRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

class LeadService implements LeadServiceInterface
{
    public function __construct(
        private readonly LeadRepositoryInterface $repository,
    ) {}

    public function create(array $data): Lead
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Lead
    {
        $lead = $this->repository->update($id, $data);

        if ($lead === null) {
            throw new NotFoundException('Lead', $id);
        }

        return $lead;
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function find(int $id): Lead
    {
        $lead = $this->repository->findById($id);

        if ($lead === null) {
            throw new NotFoundException('Lead', $id);
        }

        return $lead;
    }

    public function qualify(int $id): Lead
    {
        return $this->update($id, ['status' => 'qualified']);
    }

    public function win(int $id): Lead
    {
        return $this->update($id, ['status' => 'won', 'probability' => 100]);
    }

    public function lose(int $id): Lead
    {
        return $this->update($id, ['status' => 'lost', 'probability' => 0]);
    }
}
