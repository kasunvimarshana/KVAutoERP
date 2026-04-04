<?php

declare(strict_types=1);

namespace Modules\User\Application\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ListUsersServiceInterface
{
    public function execute(array $filters = [], int $perPage = 15, int $page = 1): LengthAwarePaginator;
}
