<?php

namespace App\Domain\Tenant\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_TRIAL = 'trial';

    const PLAN_FREE = 'free';
    const PLAN_STARTER = 'starter';
    const PLAN_PROFESSIONAL = 'professional';
    const PLAN_ENTERPRISE = 'enterprise';

    /** @var bool Disable auto-incrementing as we use UUIDs */
    public $incrementing = false;

    /** @var string UUID primary key type */
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'domain',
        'email',
        'phone',
        'status',
        'plan',
        'settings',
        'db_config',
        'cache_config',
        'mail_config',
        'broker_config',
        'trial_ends_at',
        'subscription_ends_at',
        'metadata',
    ];

    protected $casts = [
        'settings'             => 'array',
        'db_config'            => 'array',
        'cache_config'         => 'array',
        'mail_config'          => 'array',
        'broker_config'        => 'array',
        'metadata'             => 'array',
        'trial_ends_at'        => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    /** Sensitive connection fields are hidden from serialisation */
    protected $hidden = ['db_config', 'mail_config', 'broker_config'];

    // -------------------------------------------------------------------------
    // Status helpers
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isOnTrial(): bool
    {
        return $this->status === self::STATUS_TRIAL
            && $this->trial_ends_at?->isFuture();
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    // -------------------------------------------------------------------------
    // Config accessors
    // -------------------------------------------------------------------------

    public function getDbConnection(): array
    {
        return $this->db_config ?? [];
    }

    public function getCacheConfig(): array
    {
        return $this->cache_config ?? [];
    }

    public function getMailConfig(): array
    {
        return $this->mail_config ?? [];
    }

    public function getBrokerConfig(): array
    {
        return $this->broker_config ?? [];
    }
}
