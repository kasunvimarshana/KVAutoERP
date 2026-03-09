<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add tenant_id to all Passport OAuth tables to enable tenant-scoped tokens.
 * Passport tables are created by: php artisan passport:install
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $tables = [
        'oauth_auth_codes',
        'oauth_access_tokens',
        'oauth_refresh_tokens',
        'oauth_clients',
        'oauth_personal_access_clients',
    ];

    public function up(): void
    {
        // oauth_auth_codes
        if (Schema::hasTable('oauth_auth_codes') && ! Schema::hasColumn('oauth_auth_codes', 'tenant_id')) {
            Schema::table('oauth_auth_codes', function (Blueprint $table): void {
                $table->string('tenant_id', 36)->nullable()->after('user_id')->index();
            });
        }

        // oauth_access_tokens
        if (Schema::hasTable('oauth_access_tokens') && ! Schema::hasColumn('oauth_access_tokens', 'tenant_id')) {
            Schema::table('oauth_access_tokens', function (Blueprint $table): void {
                $table->string('tenant_id', 36)->nullable()->after('user_id')->index();
            });
        }

        // oauth_clients
        if (Schema::hasTable('oauth_clients') && ! Schema::hasColumn('oauth_clients', 'tenant_id')) {
            Schema::table('oauth_clients', function (Blueprint $table): void {
                $table->string('tenant_id', 36)->nullable()->after('user_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('oauth_auth_codes') && Schema::hasColumn('oauth_auth_codes', 'tenant_id')) {
            Schema::table('oauth_auth_codes', function (Blueprint $table): void {
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasTable('oauth_access_tokens') && Schema::hasColumn('oauth_access_tokens', 'tenant_id')) {
            Schema::table('oauth_access_tokens', function (Blueprint $table): void {
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasTable('oauth_clients') && Schema::hasColumn('oauth_clients', 'tenant_id')) {
            Schema::table('oauth_clients', function (Blueprint $table): void {
                $table->dropColumn('tenant_id');
            });
        }
    }
};
