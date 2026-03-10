<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'inventory_transactions';

    protected $fillable = [
        'tenant_id', 'inventory_id', 'reference_type', 'reference_id',
        'type', 'quantity', 'quantity_before', 'quantity_after', 'notes', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'quantity'        => 'integer',
            'quantity_before' => 'integer',
            'quantity_after'  => 'integer',
            'metadata'        => 'array',
        ];
    }

    public function inventory(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
}
