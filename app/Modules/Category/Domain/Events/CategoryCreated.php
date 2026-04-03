<?php
declare(strict_types=1);
namespace Modules\Category\Domain\Events;

use Modules\Category\Domain\Entities\Category;
use Modules\Core\Domain\Events\BaseEvent;

class CategoryCreated extends BaseEvent
{
    public Category $category;

    public function __construct(Category $category)
    {
        parent::__construct($category->getTenantId());
        $this->category = $category;
    }

    public function broadcastWith(): array
    {
        return [
            'id'        => $this->category->getId(),
            'name'      => $this->category->getName(),
            'slug'      => $this->category->getSlug(),
            'parent_id' => $this->category->getParentId(),
            'status'    => $this->category->getStatus(),
            'tenantId'  => $this->tenantId,
        ];
    }
}
