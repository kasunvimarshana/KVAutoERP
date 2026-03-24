<?php

namespace Modules\Core\Application\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ServiceInterface
{
    public function execute(array $data = []): mixed;

    public function find(mixed $id): mixed;

    public function list(
        array $filters = [],
        ?int $perPage = null,
        int $page = 1,
        ?string $sort = null,
        ?string $include = null
    ): LengthAwarePaginator;
}
