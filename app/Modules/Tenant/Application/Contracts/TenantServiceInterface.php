<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Contracts;

use Modules\Tenant\Domain\Entities\Tenant;

interface TenantServiceInterface
{
    public function create(array $data): Tenant;

    public function update(int $id, array $data): Tenant;

    public function suspend(int $id): void;

    public function activate(int $id): void;

    public function find(int $id): Tenant;

    public function findBySlug(string $slug): Tenant;

    /** @return Tenant[] */
    public function all(): array;
}
