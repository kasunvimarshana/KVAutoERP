<?php
declare(strict_types=1);
namespace Modules\Auth\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Core\Domain\Contracts\Repositories\RepositoryInterface;

interface PermissionRepositoryInterface extends RepositoryInterface
{
    public function findAll(): Collection;
    public function findByModule(string $module): Collection;
    public function findByIds(array $ids): Collection;
}
