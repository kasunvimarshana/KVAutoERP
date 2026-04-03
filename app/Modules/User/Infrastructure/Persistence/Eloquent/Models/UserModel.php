<?php
declare(strict_types=1);
namespace Modules\User\Infrastructure\Persistence\Eloquent\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasAudit;

class UserModel extends Authenticatable implements OAuthenticatable {
    use HasApiTokens, HasAudit, SoftDeletes;

    protected $table = 'users';
    protected $fillable = [
        'tenant_id', 'first_name', 'last_name', 'email',
        'password', 'avatar', 'preferences',
    ];
    protected $hidden = ['password', 'remember_token'];
}
