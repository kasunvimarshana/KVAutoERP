<?php

declare(strict_types=1);

namespace App\Domain\Webhook\Entities;

use App\Domain\Tenant\Entities\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string      $id
 * @property string      $tenant_id
 * @property string      $url
 * @property array       $events
 * @property string      $secret
 * @property bool        $is_active
 * @property int         $retry_count
 * @property Carbon|null $last_triggered_at
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon|null $deleted_at
 */
class WebhookSubscription extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'webhook_subscriptions';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'tenant_id',
        'url',
        'events',
        'secret',
        'is_active',
        'retry_count',
        'last_triggered_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'events'            => 'array',
        'is_active'         => 'boolean',
        'retry_count'       => 'integer',
        'last_triggered_at' => 'datetime',
    ];

    /** @var list<string> */
    protected $hidden = ['secret'];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    // -------------------------------------------------------------------------
    // Business Methods
    // -------------------------------------------------------------------------

    /**
     * Determine whether this subscription should receive the given event.
     */
    public function shouldReceive(string $event): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $events = $this->events ?? [];

        // Wildcard subscription
        if (in_array('*', $events, true)) {
            return true;
        }

        // Check exact match or wildcard prefix (e.g. "tenant.*")
        foreach ($events as $pattern) {
            if ($pattern === $event) {
                return true;
            }

            if (str_ends_with($pattern, '.*')) {
                $prefix = rtrim($pattern, '.*');

                if (str_starts_with($event, $prefix . '.')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Generate the X-Webhook-Signature header value for a given payload.
     */
    public function getSignatureHeader(string $payload): string
    {
        return 'sha256=' . hash_hmac('sha256', $payload, $this->secret);
    }
}
