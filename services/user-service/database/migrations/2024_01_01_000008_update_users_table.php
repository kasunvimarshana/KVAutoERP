<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the auto-increment id and re-create as uuid primary key
            $table->uuid('tenant_id')->nullable()->index()->after('remember_token');
            $table->uuid('organization_id')->nullable()->index()->after('tenant_id');
            $table->uuid('branch_id')->nullable()->index()->after('organization_id');
            $table->uuid('location_id')->nullable()->index()->after('branch_id');
            $table->uuid('department_id')->nullable()->index()->after('location_id');
            $table->string('status', 50)->default('active')->after('department_id');
            $table->unsignedInteger('token_version')->default(1)->after('status');
            $table->string('iam_provider', 100)->nullable()->default('local')->after('token_version');
            $table->string('external_id', 255)->nullable()->index()->after('iam_provider');
            $table->string('phone', 50)->nullable()->after('external_id');
            $table->string('avatar', 500)->nullable()->after('phone');
            $table->json('metadata')->nullable()->after('avatar');
            $table->timestamp('last_login_at')->nullable()->after('metadata');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->string('last_login_device', 255)->nullable()->after('last_login_ip');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'tenant_id',
                'organization_id',
                'branch_id',
                'location_id',
                'department_id',
                'status',
                'token_version',
                'iam_provider',
                'external_id',
                'phone',
                'avatar',
                'metadata',
                'last_login_at',
                'last_login_ip',
                'last_login_device',
            ]);
        });
    }
};
