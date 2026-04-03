<?php
declare(strict_types=1);
namespace Modules\Tenant\Application\Contracts;

interface DeleteTenantServiceInterface {
    public function execute(array $data = []): mixed;
}
