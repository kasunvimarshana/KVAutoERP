<?php

namespace App\Modules\Webhook\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'events',
        'secret',
        'is_active',
        'description',
        'headers',
        'failure_count',
        'last_triggered_at',
    ];

    protected $casts = [
        'events'            => 'array',
        'headers'           => 'array',
        'is_active'         => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    protected $hidden = [
        'secret',
    ];
}
