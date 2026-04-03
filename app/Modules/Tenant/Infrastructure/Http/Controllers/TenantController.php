<?php
declare(strict_types=1);
namespace Modules\Tenant\Infrastructure\Http\Controllers;
use Modules\Core\Application\Contracts\ServiceInterface;
use Modules\Core\Infrastructure\Http\Controllers\BaseController;
use Modules\Tenant\Application\Contracts\UploadTenantAttachmentServiceInterface;
use Modules\Tenant\Application\DTOs\CreateTenantData;
use Modules\Tenant\Infrastructure\Http\Requests\StoreTenantRequest;
use Modules\Tenant\Infrastructure\Http\Requests\UpdateTenantRequest;
use Modules\Tenant\Infrastructure\Http\Resources\TenantResource;
use Modules\Tenant\Infrastructure\Persistence\Eloquent\Models\TenantModel;

class TenantController extends BaseController {
    public function __construct(
        ServiceInterface $service,
        private UploadTenantAttachmentServiceInterface $uploadAttachment
    ) {
        parent::__construct($service, TenantResource::class, CreateTenantData::class);
    }

    protected function getModelClass(): string { return TenantModel::class; }
    public function index() {}
    public function show($id) {}
    public function store(StoreTenantRequest $request) {}
    public function update(UpdateTenantRequest $request, $id) {}
    public function destroy($id) {}
}
