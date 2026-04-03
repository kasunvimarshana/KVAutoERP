<?php
declare(strict_types=1);
namespace Modules\User\Application\Contracts;

interface SyncRolePermissionsServiceInterface {
    public function execute(array $data = []): mixed;
}
