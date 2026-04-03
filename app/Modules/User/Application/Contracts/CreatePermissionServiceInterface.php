<?php
declare(strict_types=1);
namespace Modules\User\Application\Contracts;

interface CreatePermissionServiceInterface {
    public function execute(array $data = []): mixed;
}
