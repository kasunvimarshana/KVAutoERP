<?php

declare(strict_types=1);

namespace App\Modules\Order\Infrastructure\Repositories;

use App\Core\Abstracts\Repositories\BaseRepository;
use App\Modules\Order\Domain\Models\Order;

/**
 * OrderRepository
 */
class OrderRepository extends BaseRepository
{
    protected string $model = Order::class;

    protected array $searchableColumns = ['saga_correlation_id'];

    protected array $filterableColumns = ['status', 'saga_status', 'customer_id', 'tenant_id'];

    protected array $sortableColumns = ['total_amount', 'created_at', 'updated_at'];
}
