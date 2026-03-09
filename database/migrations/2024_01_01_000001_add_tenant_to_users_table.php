<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds tenant_id, is_active, and ABAC attributes to the users table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('tenant_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('tenants')
                  ->nullOnDelete();

            $table->string('avatar')->nullable()->after('email');
            $table->json('abac_attributes')->nullable()->after('avatar');  // ABAC attribute bag
            $table->boolean('is_active')->default(true)->after('attributes');

            $table->index('tenant_id');
            $table->index(['tenant_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'avatar', 'abac_attributes', 'is_active']);
        });
    }
};
