<?php

namespace App\Domain\Auth\Entities;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

/**
 * Minimal user stub used by the Order Service to validate
 * Passport tokens issued by the Auth Service.
 *
 * The Order Service does not store users locally; it simply
 * introspects the bearer token claims (tenant_id, roles, etc.)
 * forwarded via HTTP headers from the API Gateway.
 */
class ServiceUser extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'users';

    protected $fillable = [
        'id',
        'tenant_id',
        'email',
        'name',
    ];
}
