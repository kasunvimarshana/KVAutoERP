<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

/**
 * Data Transfer Object for tenant configuration updates.
 *
 * Encapsulates partial configuration updates for tenants.
 * Used in PATCH operations to update specific configuration aspects
 * without modifying the entire tenant entity.
 */
class TenantConfigData extends BaseDto
{
    /**
     * Database connection configuration (optional).
     * Only updated if provided.
     */
    public ?array $database_config = null;

    /**
     * Mail service configuration (optional).
     * Only updated if provided.
     */
    public ?array $mail_config = null;

    /**
     * Cache store configuration (optional).
     * Only updated if provided.
     */
    public ?array $cache_config = null;

    /**
     * Queue driver configuration (optional).
     * Only updated if provided.
     */
    public ?array $queue_config = null;

    /**
     * Feature flags for capability toggling (optional).
     * Only updated if provided.
     */
    public ?array $feature_flags = null;

    /**
     * API keys for third-party integrations (optional).
     * Only updated if provided.
     */
    public ?array $api_keys = null;

    /**
     * Miscellaneous tenant settings (optional).
     * Only updated if provided.
     */
    public ?array $settings = null;

    /**
     * Validation rules for input data.
     *
     * At least one configuration must be provided.
     * All fields are optional but individual sub-fields are validated when present.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'database_config' => 'sometimes|array',
            'database_config.driver' => 'required_with:database_config|string|in:mysql,pgsql,sqlite,sqlsrv',
            'database_config.host' => 'required_with:database_config|string|max:255',
            'database_config.port' => 'required_with:database_config|integer|min:1|max:65535',
            'database_config.database' => 'required_with:database_config|string|max:255',
            'database_config.username' => 'required_with:database_config|string|max:255',
            'database_config.password' => 'required_with:database_config|string',
            'mail_config' => 'sometimes|array',
            'mail_config.driver' => 'required_with:mail_config|string|in:smtp,sendmail,mailgun,ses,log,array',
            'mail_config.host' => 'required_with:mail_config|string|max:255',
            'mail_config.port' => 'required_with:mail_config|integer|min:1|max:65535',
            'mail_config.username' => 'required_with:mail_config|string|max:255',
            'mail_config.password' => 'required_with:mail_config|string',
            'mail_config.from' => 'required_with:mail_config|email|max:255',
            'cache_config' => 'sometimes|array',
            'cache_config.driver' => 'required_with:cache_config|string|in:file,array,database,memcached,redis',
            'queue_config' => 'sometimes|array',
            'queue_config.driver' => 'required_with:queue_config|string|in:database,redis,beanstalkd,sqs,fifo,null',
            'feature_flags' => 'sometimes|array',
            'api_keys' => 'sometimes|array',
            'settings' => 'sometimes|array',
        ];
    }

    /**
     * Check if any configuration has been provided for update.
     *
     * @return bool True if at least one configuration is not null.
     */
    public function hasAnyConfig(): bool
    {
        return $this->database_config !== null
            || $this->mail_config !== null
            || $this->cache_config !== null
            || $this->queue_config !== null
            || $this->feature_flags !== null
            || $this->api_keys !== null
            || $this->settings !== null;
    }

    /**
     * Get only the configuration values that are being updated.
     * Useful for partial updates.
     *
     * @return array<string, mixed>
     */
    public function getConfigValues(): array
    {
        return array_filter([
            'database_config' => $this->database_config,
            'mail_config' => $this->mail_config,
            'cache_config' => $this->cache_config,
            'queue_config' => $this->queue_config,
            'feature_flags' => $this->feature_flags,
            'api_keys' => $this->api_keys,
            'settings' => $this->settings,
        ], fn ($value) => $value !== null);
    }
}
