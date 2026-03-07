<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    use HasFactory, HasUuids;

    public const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'event',
        'url',
        'payload',
        'response',
        'status',
        'attempts',
    ];

    protected $casts = [
        'payload'  => 'array',
        'response' => 'array',
        'attempts' => 'integer',
    ];
}
