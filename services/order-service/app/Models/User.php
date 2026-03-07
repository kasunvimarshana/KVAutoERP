<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasUuids;

    protected $table = 'users';

    protected $fillable = ['id', 'tenant_id', 'name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];
}
