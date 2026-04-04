<?php

declare(strict_types=1);

namespace Modules\User\Application\Contracts;

use Modules\User\Domain\Entities\User;

interface GetUserServiceInterface
{
    public function execute(int $id): User;
}
