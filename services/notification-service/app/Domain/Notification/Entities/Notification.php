<?php

namespace App\Domain\Notification\Entities;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    const CHANNEL_EMAIL   = 'email';
    const CHANNEL_SLACK   = 'slack';
    const CHANNEL_WEBHOOK = 'webhook';
    const CHANNEL_PUSH    = 'push';

    const STATUS_PENDING = 'pending';
    const STATUS_SENT    = 'sent';
    const STATUS_FAILED  = 'failed';

    protected $fillable = [
        'tenant_id',
        'channel',
        'recipient',
        'subject',
        'template',
        'payload',
        'status',
        'attempts',
        'sent_at',
        'error_message',
        'event_type',
        'metadata',
    ];

    protected $casts = [
        'payload'    => 'array',
        'metadata'   => 'array',
        'sent_at'    => 'datetime',
        'attempts'   => 'integer',
    ];

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }
}
