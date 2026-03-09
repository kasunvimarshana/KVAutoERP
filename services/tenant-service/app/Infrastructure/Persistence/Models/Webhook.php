<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Webhook Eloquent Model.
 *
 * Maps to the `webhooks` table.
 */
class Webhook extends Model
{
    use HasUuids;

    /** @var string */
    protected $table = 'webhooks';

    /** @var string */
    protected $primaryKey = 'id';

    /** @var bool */
    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /** @var array<int, string> */
    protected $fillable = [
        'id',
        'tenant_id',
        'url',
        'events',
        'secret',
        'is_active',
        'last_triggered_at',
        'failures_count',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'events'            => 'array',
        'is_active'         => 'boolean',
        'last_triggered_at' => 'datetime',
        'failures_count'    => 'integer',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    // ──────────────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────────────

    /**
     * @return BelongsTo<Tenant, Webhook>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    /**
     * @return HasMany<WebhookDelivery>
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class, 'webhook_id', 'id');
    }
}
