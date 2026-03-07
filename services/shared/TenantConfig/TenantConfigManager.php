<?php

namespace App\Shared\TenantConfig;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Manages per-tenant runtime configuration overrides.
 * Loads tenant config from DB, caches it in Redis,
 * and applies it to Laravel's config() at runtime.
 */
class TenantConfigManager
{
    private const CACHE_TTL = 300; // 5 minutes
    private const CACHE_PREFIX = 'tenant_config:';

    public function loadConfig(string $tenantId): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . $tenantId,
            self::CACHE_TTL,
            function () use ($tenantId) {
                $tenant = DB::table('tenants')->where('id', $tenantId)->first();
                if (!$tenant) {
                    return [];
                }
                return is_string($tenant->config)
                    ? json_decode($tenant->config, true) ?? []
                    : (array) ($tenant->config ?? []);
            }
        );
    }

    public function getMailConfig(string $tenantId): array
    {
        $config = $this->loadConfig($tenantId);
        return array_merge(
            [
                'driver'     => config('mail.default', 'smtp'),
                'host'       => config('mail.mailers.smtp.host'),
                'port'       => config('mail.mailers.smtp.port'),
                'username'   => config('mail.mailers.smtp.username'),
                'password'   => config('mail.mailers.smtp.password'),
                'encryption' => config('mail.mailers.smtp.encryption'),
                'from'       => config('mail.from'),
            ],
            $config['mail'] ?? []
        );
    }

    public function getPaymentConfig(string $tenantId): array
    {
        $config = $this->loadConfig($tenantId);
        return array_merge(
            [
                'provider'   => 'stripe',
                'public_key' => '',
                'secret_key' => '',
                'webhook_secret' => '',
                'currency'   => 'USD',
            ],
            $config['payment'] ?? []
        );
    }

    public function getNotificationConfig(string $tenantId): array
    {
        $config = $this->loadConfig($tenantId);
        return array_merge(
            [
                'channels'       => ['mail'],
                'slack_webhook'  => '',
                'sms_provider'   => '',
                'sms_api_key'    => '',
                'push_enabled'   => false,
            ],
            $config['notifications'] ?? []
        );
    }

    /**
     * Apply tenant-specific settings to Laravel config() at runtime.
     */
    public function overrideConfig(string $tenantId): void
    {
        $tenantConfig = $this->loadConfig($tenantId);

        if (empty($tenantConfig)) {
            return;
        }

        // Override mail config
        if (!empty($tenantConfig['mail'])) {
            $mail = $tenantConfig['mail'];
            if (isset($mail['host']))       Config::set('mail.mailers.smtp.host', $mail['host']);
            if (isset($mail['port']))       Config::set('mail.mailers.smtp.port', $mail['port']);
            if (isset($mail['username']))   Config::set('mail.mailers.smtp.username', $mail['username']);
            if (isset($mail['password']))   Config::set('mail.mailers.smtp.password', $mail['password']);
            if (isset($mail['encryption'])) Config::set('mail.mailers.smtp.encryption', $mail['encryption']);
            if (isset($mail['from_address'])) {
                Config::set('mail.from.address', $mail['from_address']);
                Config::set('mail.from.name', $mail['from_name'] ?? config('mail.from.name'));
            }
        }

        // Override any other tenant-specific config keys
        foreach ($tenantConfig as $key => $value) {
            if (!in_array($key, ['mail', 'payment', 'notifications'])) {
                Config::set("tenant.{$key}", $value);
            }
        }
    }

    public function flushCache(string $tenantId): void
    {
        Cache::forget(self::CACHE_PREFIX . $tenantId);
    }
}
