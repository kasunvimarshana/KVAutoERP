<?php declare(strict_types=1);
namespace Modules\Auth\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Laravel\Passport\HasApiTokens;
class UserModel extends BaseModel {
    use HasApiTokens;
    protected $table = 'users';
    protected $fillable = ['tenant_id','name','email','password','role','is_active','email_verified_at'];
    protected $hidden = ['password','remember_token'];
    protected $casts = ['is_active'=>'boolean','email_verified_at'=>'datetime','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
