<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'user_profiles';

    protected $fillable = [
        'auth_user_id',
        'tenant_id',
        'first_name',
        'last_name',
        'phone',
        'address',
        'avatar_url',
        'preferences',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'address'     => 'array',
            'preferences' => 'array',
            'metadata'    => 'array',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
