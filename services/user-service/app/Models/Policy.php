<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Policy extends Model
{
    use HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'effect',
        'action',
        'subject_conditions',
        'resource_conditions',
        'environment_conditions',
        'is_active',
        'priority',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'subject_conditions'     => 'array',
            'resource_conditions'    => 'array',
            'environment_conditions' => 'array',
            'is_active'              => 'boolean',
            'priority'               => 'integer',
        ];
    }

    public function isAllow(): bool
    {
        return $this->effect === 'allow';
    }

    public function isDeny(): bool
    {
        return $this->effect === 'deny';
    }
}
