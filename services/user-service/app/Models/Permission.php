<?php

declare(strict_types=1);

namespace App\Models;

use KvEnterprise\SharedKernel\Models\TenantAwareModel;

/**
 * Permission domain entity.
 *
 * Represents a fine-grained ABAC permission scoped to a tenant.
 * Permissions are associated with a module and action pair (e.g. users.manage).
 *
 * @property string      $id
 * @property string      $tenant_id
 * @property string      $name
 * @property string      $slug
 * @property string      $module
 * @property string      $action
 * @property string|null $description
 * @property bool        $is_system
 * @property array|null  $metadata
 * @property string|null $created_by
 * @property string|null $updated_by
 */
final class Permission extends TenantAwareModel
{
    /** @var string */
    protected $table = 'permissions';

    /** @var array<string, string> */
    protected $casts = [
        'is_system'  => 'boolean',
        'metadata'   => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
