<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'tenants';

    protected $fillable = [
        'name', 'slug', 'domain', 'status', 'plan', 'config', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'config'   => 'array',
            'metadata' => 'array',
        ];
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
