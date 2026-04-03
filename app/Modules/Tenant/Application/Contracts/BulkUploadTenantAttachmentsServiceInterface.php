<?php
declare(strict_types=1);
namespace Modules\Tenant\Application\Contracts;

interface BulkUploadTenantAttachmentsServiceInterface {
    public function execute(array $data = []): mixed;
}
