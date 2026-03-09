<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Entities;

use App\Domain\Tenant\Events\TenantActivated;
use App\Domain\Tenant\Events\TenantConfigUpdated;
use App\Domain\Tenant\Events\TenantCreated;
use App\Domain\Tenant\Events\TenantDeleted;
use App\Domain\Tenant\Events\TenantSuspended;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string      $id
 * @property string      $name
 * @property string      $slug
 * @property string|null $domain
 * @property string      $status
 * @property string      $plan
 * @property array|null  $settings
 * @property array|null  $config
 * @property array|null  $database_config
 * @property array|null  $mail_config
 * @property array|null  $cache_config
 * @property array|null  $broker_config
 * @property int         $max_users
 * @property int         $max_organizations
 * @property Carbon|null $trial_ends_at
 * @property Carbon|null $subscription_ends_at
 * @property array|null  $metadata
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon|null $deleted_at
 */
class Tenant extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public const STATUS_ACTIVE    = 'active';
    public const STATUS_INACTIVE  = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_PENDING   = 'pending';

    public const PLAN_FREE         = 'free';
    public const PLAN_STARTER      = 'starter';
    public const PLAN_PROFESSIONAL = 'professional';
    public const PLAN_ENTERPRISE   = 'enterprise';

    protected $table = 'tenants';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'status',
        'plan',
        'settings',
        'config',
        'database_config',
        'mail_config',
        'cache_config',
        'broker_config',
        'max_users',
        'max_organizations',
        'trial_ends_at',
        'subscription_ends_at',
        'metadata',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'settings'             => 'array',
        'config'               => 'array',
        'database_config'      => 'array',
        'mail_config'          => 'array',
        'cache_config'         => 'array',
        'broker_config'        => 'array',
        'metadata'             => 'array',
        'max_users'            => 'integer',
        'max_organizations'    => 'integer',
        'trial_ends_at'        => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    /** @var list<string> */
    protected $hidden = [
        'database_config',
        'mail_config',
        'cache_config',
        'broker_config',
    ];

    // -------------------------------------------------------------------------
    // Boot / Domain Events
    // -------------------------------------------------------------------------

    protected static function boot(): void
    {
        parent::boot();

        static::created(function (Tenant $tenant): void {
            $tenant->fireModelEvent('tenantCreated', false);
            event(new TenantCreated($tenant));
        });

        static::deleted(function (Tenant $tenant): void {
            event(new TenantDeleted($tenant));
        });
    }

    // -------------------------------------------------------------------------
    // Business Methods
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    public function isPlanActive(): bool
    {
        if ($this->subscription_ends_at === null) {
            return true; // No expiry set (free / lifetime)
        }

        return $this->subscription_ends_at->isFuture();
    }

    public function canAddUser(int $currentCount): bool
    {
        if ($this->max_users <= 0) {
            return true; // Unlimited
        }

        return $currentCount < $this->max_users;
    }

    public function canAddOrganization(int $currentCount): bool
    {
        if ($this->max_organizations <= 0) {
            return true; // Unlimited
        }

        return $currentCount < $this->max_organizations;
    }

    public function getConfig(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }

    public function setConfig(string $key, mixed $value): void
    {
        $config = $this->config ?? [];
        data_set($config, $key, $value);
        $this->config = $config;
        $this->save();

        event(new TenantConfigUpdated($this, [$key => $value]));
    }

    public function getDatabaseConnection(): string
    {
        if (empty($this->database_config)) {
            return config('database.default');
        }

        return "tenant_{$this->slug}";
    }

    public function getMailConfig(): array
    {
        return $this->mail_config ?? [];
    }

    public function getCacheConfig(): array
    {
        return $this->cache_config ?? [];
    }

    public function getBrokerConfig(): array
    {
        return $this->broker_config ?? [];
    }

    public function activate(): void
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();

        event(new TenantActivated($this));
    }

    public function suspend(string $reason = ''): void
    {
        $this->status = self::STATUS_SUSPENDED;

        if ($reason !== '') {
            $metadata                    = $this->metadata ?? [];
            $metadata['suspended_reason'] = $reason;
            $metadata['suspended_at']     = now()->toIso8601String();
            $this->metadata              = $metadata;
        }

        $this->save();

        event(new TenantSuspended($this, $reason));
    }
}
