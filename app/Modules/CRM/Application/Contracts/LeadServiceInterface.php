<?php

declare(strict_types=1);

namespace Modules\CRM\Application\Contracts;

use Modules\CRM\Domain\Entities\Lead;

interface LeadServiceInterface
{
    public function create(array $data): Lead;

    public function update(int $id, array $data): Lead;

    public function delete(int $id): bool;

    public function find(int $id): Lead;

    public function qualify(int $id): Lead;

    public function win(int $id): Lead;

    public function lose(int $id): Lead;
}
