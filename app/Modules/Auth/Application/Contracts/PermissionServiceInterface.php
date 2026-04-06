<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Contracts;

use Illuminate\Support\Collection;

interface PermissionServiceInterface
{
    public function findAll(): Collection;
    public function findByModule(string $module): Collection;
    public function seedDefaultPermissions(array $permissions): void;
}
