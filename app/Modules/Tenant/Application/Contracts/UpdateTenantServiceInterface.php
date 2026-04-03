<?php
declare(strict_types=1);
namespace Modules\Tenant\Application\Contracts;

interface UpdateTenantServiceInterface {
    public function execute(array $data = []): mixed;
}
