<?php
declare(strict_types=1);
namespace Modules\Category\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;

class CategoryDeleted extends BaseEvent
{
    public int $categoryId;

    public function __construct(int $categoryId, int $tenantId)
    {
        parent::__construct($tenantId);
        $this->categoryId = $categoryId;
    }

    public function broadcastWith(): array
    {
        return [
            'categoryId' => $this->categoryId,
            'tenantId'   => $this->tenantId,
        ];
    }
}
