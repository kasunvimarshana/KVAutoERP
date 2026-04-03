<?php
declare(strict_types=1);
namespace Modules\Brand\Domain\Events;
use Modules\Brand\Domain\Entities\Brand;
use Modules\Core\Domain\Events\BaseEvent;

class BrandCreated extends BaseEvent
{
    public Brand $brand;
    public function __construct(Brand $brand)
    {
        parent::__construct($brand->getTenantId());
        $this->brand = $brand;
    }
    public function broadcastWith(): array
    {
        return ['id' => $this->brand->getId(), 'name' => $this->brand->getName(), 'slug' => $this->brand->getSlug(), 'status' => $this->brand->getStatus(), 'tenantId' => $this->tenantId];
    }
}
