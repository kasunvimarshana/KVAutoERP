<?php

declare(strict_types=1);

namespace Modules\CRM\Application\Contracts;

use Modules\CRM\Domain\Entities\Activity;

interface ActivityServiceInterface
{
    public function create(array $data): Activity;

    public function update(int $id, array $data): Activity;

    public function delete(int $id): bool;

    public function find(int $id): Activity;

    public function complete(int $id): Activity;

    public function cancel(int $id): Activity;
}
