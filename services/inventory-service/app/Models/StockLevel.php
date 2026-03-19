<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'bin_location_id',
        'lot_id',
        'serial_id',
        'quantity', // Current Physical Stock
        'reserved_quantity', // Reserved Stock
        'available_quantity', // Quantity - Reserved
        'uom_id',
        'status', // Available, Quarantined, Damaged
        'last_ledger_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:10',
        'reserved_quantity' => 'decimal:10',
        'available_quantity' => 'decimal:10',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function binLocation(): BelongsTo
    {
        return $this->belongsTo(BinLocation.class);
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }
}
