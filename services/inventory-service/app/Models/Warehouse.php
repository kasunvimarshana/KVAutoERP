<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'type', // Distribution Center, Retail, Cold Storage, etc.
        'status',
        'is_default',
    ];

    public function bins(): HasMany
    {
        return $this->hasMany(BinLocation.class);
    }
}
