<?php

declare(strict_types=1);

namespace App\Modules\Auth\Infrastructure\Repositories;

use App\Core\Abstracts\Repositories\BaseRepository;
use App\Modules\Auth\Domain\Models\User;

/**
 * UserRepository
 *
 * Tenant-scoped user repository.
 */
class UserRepository extends BaseRepository
{
    protected string $model = User::class;

    protected array $searchableColumns = ['name', 'email'];

    protected array $filterableColumns = ['tenant_id', 'is_active'];

    protected array $sortableColumns = ['name', 'email', 'created_at', 'updated_at'];
}
