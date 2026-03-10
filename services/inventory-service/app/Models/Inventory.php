<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'inventories';

    protected $fillable = [
        'tenant_id', 'product_id', 'product_code', 'product_name', 'category_id',
        'quantity_on_hand', 'quantity_reserved', 'quantity_available',
        'reorder_point', 'reorder_quantity', 'location', 'status', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'quantity_on_hand'   => 'integer',
            'quantity_reserved'  => 'integer',
            'quantity_available' => 'integer',
            'reorder_point'      => 'integer',
            'reorder_quantity'   => 'integer',
            'metadata'           => 'array',
        ];
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'inventory_id');
    }

    public function isLowStock(): bool
    {
        return $this->quantity_available <= ($this->reorder_point ?? 0);
    }
}
