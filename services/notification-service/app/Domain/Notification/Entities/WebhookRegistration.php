<?php

namespace App\Domain\Notification\Entities;

use Illuminate\Database\Eloquent\Model;

class WebhookRegistration extends Model
{
    protected $table = 'webhook_registrations';

    protected $fillable = [
        'tenant_id',
        'url',
        'events',
        'secret',
        'is_active',
        'description',
        'metadata',
    ];

    protected $casts = [
        'events'    => 'array',
        'metadata'  => 'array',
        'is_active' => 'boolean',
    ];

    /** @var array Never expose the signing secret */
    protected $hidden = ['secret'];

    public function logs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WebhookLog::class, 'webhook_id');
    }
}
