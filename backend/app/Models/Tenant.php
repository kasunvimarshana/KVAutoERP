<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'plan',
        'is_active',
        'settings',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings'  => 'array',
        'metadata'  => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
