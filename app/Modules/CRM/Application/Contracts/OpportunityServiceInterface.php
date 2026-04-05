<?php

declare(strict_types=1);

namespace Modules\CRM\Application\Contracts;

use Modules\CRM\Domain\Entities\Opportunity;

interface OpportunityServiceInterface
{
    public function create(array $data): Opportunity;

    public function update(int $id, array $data): Opportunity;

    public function delete(int $id): bool;

    public function find(int $id): Opportunity;

    public function advanceStage(int $id): Opportunity;

    public function closeWon(int $id): Opportunity;

    public function closeLost(int $id): Opportunity;
}
