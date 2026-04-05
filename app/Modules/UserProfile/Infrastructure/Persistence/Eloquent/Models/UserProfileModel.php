<?php

declare(strict_types=1);

namespace Modules\UserProfile\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class UserProfileModel extends BaseModel
{
    protected $table = 'user_profiles';

    protected $fillable = [
        'user_id',
        'avatar',
        'bio',
        'phone',
        'address',
        'preferences',
        'timezone',
        'locale',
    ];

    protected $casts = [
        'id'          => 'int',
        'user_id'     => 'int',
        'address'     => 'array',
        'preferences' => 'array',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }
}
