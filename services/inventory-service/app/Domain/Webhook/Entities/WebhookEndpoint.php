<?php

declare(strict_types=1);

namespace App\Domain\Webhook\Entities;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Webhook Endpoint Entity.
 *
 * Stores tenant-configured webhook delivery targets.
 */
class WebhookEndpoint extends Model
{
    use HasUuids;

    protected $table = 'webhook_endpoints';

    protected $fillable = [
        'tenant_id',
        'url',
        'events',     // ['*'] or specific events like ['inventory.created']
        'secret',
        'is_active',
        'description',
    ];

    protected $casts = [
        'events'    => 'array',
        'is_active' => 'boolean',
    ];

    protected $hidden = ['secret'];

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }
}
