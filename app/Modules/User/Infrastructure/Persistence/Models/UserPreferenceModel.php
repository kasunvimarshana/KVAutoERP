<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreferenceModel extends Model
{
    protected $table = 'user_preferences';

    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    protected $casts = [
        'id'      => 'int',
        'user_id' => 'int',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }
}
