<?php
declare(strict_types=1);
namespace Modules\Tenant\Application\Contracts;

interface DeleteTenantAttachmentServiceInterface {
    public function execute(array $data = []): mixed;
}
