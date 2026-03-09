<?php

declare(strict_types=1);

namespace App\Domain\Order\Repositories\Interfaces;

use App\Domain\Order\Entities\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Order Repository Interface.
 */
interface OrderRepositoryInterface
{
    /** @param  array<string, mixed> $params */
    public function all(array $params = []): LengthAwarePaginator|Collection;

    public function find(string $id): ?Order;

    /** @param  array<string, mixed> $data */
    public function create(array $data): Order;

    /** @param  array<string, mixed> $data */
    public function update(string $id, array $data): Order;

    public function delete(string $id): bool;
}
