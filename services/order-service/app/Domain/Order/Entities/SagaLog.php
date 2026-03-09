<?php

declare(strict_types=1);

namespace App\Domain\Order\Entities;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Saga Log Entity.
 *
 * Immutable audit trail for saga step execution and compensation.
 * Enables saga state recovery after service restarts.
 */
class SagaLog extends Model
{
    use HasUuids;

    protected $table = 'saga_logs';

    public $timestamps = true;
    public $updatedAt = null; // Logs are immutable

    protected $fillable = [
        'saga_transaction_id',
        'order_id',
        'step_name',
        'action',       // execute | compensate
        'status',       // started | completed | failed
        'payload',
        'error_message',
        'duration_ms',
    ];

    protected $casts = [
        'payload'     => 'array',
        'duration_ms' => 'integer',
    ];
}
