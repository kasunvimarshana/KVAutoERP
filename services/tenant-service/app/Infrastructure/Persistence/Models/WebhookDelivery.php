<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * WebhookDelivery Eloquent Model.
 *
 * Maps to the `webhook_deliveries` table.
 * Tracks individual webhook dispatch attempts.
 */
class WebhookDelivery extends Model
{
    use HasUuids;

    /** @var string */
    protected $table = 'webhook_deliveries';

    /** @var string */
    protected $primaryKey = 'id';

    /** @var bool */
    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /** @var array<int, string> */
    protected $fillable = [
        'id',
        'webhook_id',
        'tenant_id',
        'event',
        'payload',
        'response_status',
        'response_body',
        'delivered_at',
        'failed_at',
        'attempt_count',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'payload'         => 'array',
        'response_status' => 'integer',
        'attempt_count'   => 'integer',
        'delivered_at'    => 'datetime',
        'failed_at'       => 'datetime',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    // ──────────────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────────────

    /**
     * @return BelongsTo<Webhook, WebhookDelivery>
     */
    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class, 'webhook_id', 'id');
    }

    /**
     * @return BelongsTo<Tenant, WebhookDelivery>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
}
