<?php
declare(strict_types=1);
namespace Modules\User\Application\Contracts;

interface DeletePermissionServiceInterface {
    public function execute(array $data = []): mixed;
}
