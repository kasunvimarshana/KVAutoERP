<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Auth\Application\Contracts\UpdateRoleServiceInterface;
use Modules\Auth\Application\DTOs\UpdateRoleData;
use Modules\Auth\Domain\Entities\Role;
use Modules\Auth\Domain\Events\RoleUpdated;
use Modules\Auth\Domain\Exceptions\RoleNotFoundException;
use Modules\Auth\Domain\Repositories\RoleRepositoryInterface;

class UpdateRoleService implements UpdateRoleServiceInterface
{
    public function __construct(
        private readonly RoleRepositoryInterface $repository,
    ) {}

    public function execute(int $id, UpdateRoleData $data): Role
    {
        return DB::transaction(function () use ($id, $data): Role {
            if ($this->repository->findById($id) === null) {
                throw new RoleNotFoundException($id);
            }

            $payload = array_filter([
                'name'        => $data->name,
                'slug'        => $data->slug,
                'description' => $data->description,
            ], fn ($v) => $v !== null);

            $role = $this->repository->update($id, $payload);

            Event::dispatch(new RoleUpdated($role));

            return $role;
        });
    }
}
