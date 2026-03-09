<?php

declare(strict_types=1);

namespace App\Domain\User\Entities;

use App\Domain\Organization\Entities\Organization;
use App\Domain\Tenant\Entities\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Passport\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string                    $id
 * @property string                    $tenant_id
 * @property string|null               $organization_id
 * @property string                    $name
 * @property string                    $email
 * @property string                    $password
 * @property string                    $status
 * @property Carbon|null               $email_verified_at
 * @property string|null               $two_factor_secret
 * @property bool                      $two_factor_enabled
 * @property array|null                $device_tokens
 * @property Carbon|null               $last_login_at
 * @property array|null                $metadata
 * @property Carbon                    $created_at
 * @property Carbon                    $updated_at
 * @property Carbon|null               $deleted_at
 * @property-read Tenant               $tenant
 * @property-read Organization|null    $organization
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use HasUuids;
    use LogsActivity;
    use Notifiable;
    use SoftDeletes;

    public const STATUS_ACTIVE    = 'active';
    public const STATUS_INACTIVE  = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';

    /** @var string */
    protected $table = 'users';

    /** @var string */
    protected $primaryKey = 'id';

    /** @var bool */
    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'tenant_id',
        'organization_id',
        'name',
        'email',
        'password',
        'status',
        'email_verified_at',
        'two_factor_secret',
        'two_factor_enabled',
        'device_tokens',
        'last_login_at',
        'metadata',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'email_verified_at'  => 'datetime',
        'two_factor_enabled' => 'boolean',
        'device_tokens'      => 'array',
        'last_login_at'      => 'datetime',
        'metadata'           => 'array',
    ];

    /**
     * The "guard_name" attribute for Spatie permissions must be scoped per tenant.
     * We override it to use the default 'api' guard.
     */
    protected string $guard_name = 'api';

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function devices(): HasMany
    {
        // UserDevice model is an optional extension for device management.
        // The device_tokens JSON column on users handles basic device tracking.
        return $this->hasMany(\App\Domain\User\Entities\User::class, 'id')->whereRaw('1=0');
    }

    // -------------------------------------------------------------------------
    // Business Methods
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function enableTwoFactor(string $secret): void
    {
        $this->two_factor_secret  = encrypt($secret);
        $this->two_factor_enabled = true;
        $this->save();
    }

    public function disableTwoFactor(): void
    {
        $this->two_factor_secret  = null;
        $this->two_factor_enabled = false;
        $this->save();
    }

    public function recordLogin(): void
    {
        $this->last_login_at = Carbon::now();
        $this->save();
    }

    public function activate(): void
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    public function suspend(): void
    {
        $this->status = self::STATUS_SUSPENDED;
        $this->save();
    }

    public function deactivate(): void
    {
        $this->status = self::STATUS_INACTIVE;
        $this->save();
    }

    /**
     * Add a device token for push notifications.
     */
    public function addDeviceToken(string $token, string $platform): void
    {
        $tokens   = $this->device_tokens ?? [];
        $tokens[] = ['token' => $token, 'platform' => $platform, 'added_at' => Carbon::now()->toIso8601String()];

        $this->device_tokens = $tokens;
        $this->save();
    }

    /**
     * Get a metadata value by dot-notation key.
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        return data_get($this->metadata, $key, $default);
    }

    /**
     * Set a metadata value by dot-notation key.
     */
    public function setMeta(string $key, mixed $value): void
    {
        $metadata = $this->metadata ?? [];
        data_set($metadata, $key, $value);
        $this->metadata = $metadata;
        $this->save();
    }

    // -------------------------------------------------------------------------
    // Activity Log
    // -------------------------------------------------------------------------

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('user');
    }
}
