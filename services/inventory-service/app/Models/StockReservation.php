<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'bin_location_id',
        'lot_id',
        'serial_id',
        'quantity',
        'uom_id',
        'reference_type', // Sales Order, Work Order
        'reference_id',
        'reference_number',
        'expiry_date', // When the reservation expires
        'status', // Active, Fulfilled, Expired, Cancelled
    ];

    protected $casts = [
        'quantity' => 'decimal:10',
        'expiry_date' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
