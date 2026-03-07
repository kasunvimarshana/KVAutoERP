<?php

namespace App\Models;

use App\Core\Tenant\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'sku',
        'name',
        'description',
        'price',
        'category',
        'attributes',
        'is_active',
    ];

    protected $casts = [
        'price'      => 'decimal:2',
        'attributes' => 'array',
        'is_active'  => 'boolean',
    ];

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}
