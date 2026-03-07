<?php

namespace App\Domain\Notification\Entities;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $table = 'webhook_logs';

    protected $fillable = [
        'webhook_id',
        'tenant_id',
        'event',
        'payload',
        'response_status',
        'response_body',
        'attempts',
        'delivered',
        'error_message',
        'delivered_at',
    ];

    protected $casts = [
        'payload'      => 'array',
        'delivered'    => 'boolean',
        'attempts'     => 'integer',
        'delivered_at' => 'datetime',
    ];

    public function webhook(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WebhookRegistration::class, 'webhook_id');
    }
}
