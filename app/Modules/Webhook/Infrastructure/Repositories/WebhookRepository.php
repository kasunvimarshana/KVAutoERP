<?php

declare(strict_types=1);

namespace App\Modules\Webhook\Infrastructure\Repositories;

use App\Core\Abstracts\Repositories\BaseRepository;
use App\Modules\Webhook\Domain\Models\Webhook;

class WebhookRepository extends BaseRepository
{
    protected string $model = Webhook::class;

    protected array $filterableColumns = ['tenant_id', 'is_active'];

    protected array $sortableColumns = ['created_at', 'updated_at'];

    /**
     * Return active webhooks subscribed to a specific event.
     *
     * @param  string $event  e.g. "order.created"
     * @return \Illuminate\Database\Eloquent\Collection<int, Webhook>
     */
    public function findByEvent(string $event): \Illuminate\Database\Eloquent\Collection
    {
        return $this->newQuery()
            ->where('is_active', true)
            ->whereJsonContains('events', $event)
            ->get();
    }
}
