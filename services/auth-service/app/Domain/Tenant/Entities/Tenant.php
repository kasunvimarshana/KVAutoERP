<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Entities;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * @property string      $id
 * @property string      $name
 * @property string      $slug
 * @property string|null $domain
 * @property string|null $database_name
 * @property string      $status
 * @property array|null  $settings
 * @property array|null  $config
 * @property int         $max_users
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon|null $deleted_at
 */
class Tenant extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_TRIAL    = 'trial';
    public const STATUS_SUSPENDED = 'suspended';

    /** @var string */
    protected $table = 'tenants';

    /** @var string */
    protected $primaryKey = 'id';

    /** @var bool */
    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'database_name',
        'status',
        'settings',
        'config',
        'max_users',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'settings' => 'array',
        'config'   => 'array',
        'max_users' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Business Methods
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isTrial(): bool
    {
        return $this->status === self::STATUS_TRIAL;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Get a config value using dot-notation.
     */
    public function getConfig(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * Set a config value using dot-notation, persisting to the database.
     */
    public function setConfig(string $key, mixed $value): void
    {
        $config = $this->config ?? [];
        data_set($config, $key, $value);
        $this->config = $config;
        $this->save();
    }

    /**
     * Get the database connection name for this tenant.
     * If database_name is set, returns a dynamic per-tenant connection;
     * otherwise falls back to the default connection.
     */
    public function getDatabaseConnection(): string
    {
        if (empty($this->database_name)) {
            return config('database.default');
        }

        $connection = "tenant_{$this->slug}";

        // Dynamically register connection configuration if not already set.
        if (! config("database.connections.{$connection}")) {
            $default = config('database.connections.' . config('database.default'));

            if (! is_array($default)) {
                throw new InvalidArgumentException('Default database connection configuration is missing.');
            }

            config([
                "database.connections.{$connection}" => array_merge($default, [
                    'database' => $this->database_name,
                ]),
            ]);
        }

        return $connection;
    }

    /**
     * Get a settings value using dot-notation.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }
}
