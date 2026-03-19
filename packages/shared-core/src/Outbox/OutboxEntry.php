<?php

namespace Shared\Core\Outbox;

use Illuminate\Database\Eloquent\Model;

class OutboxEntry extends Model
{
    protected $table = 'outbox_entries';

    protected $fillable = [
        'event_name',
        'payload',
        'status', // pending, processed, failed
        'attempts',
        'error_message',
        'tenant_id',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
