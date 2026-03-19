<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLedger extends Model
{
    use HasFactory;

    protected $table = 'stock_ledger';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'bin_location_id',
        'lot_id',
        'serial_id',
        'quantity', // Positive for IN, Negative for OUT
        'uom_id',
        'transaction_type', // Purchase Receipt, Sales Shipment, Transfer, Adjustment, Return
        'reference_type', // Order, Invoice, Receipt, TransferOrder
        'reference_id',
        'reference_number',
        'cost_at_transaction',
        'tenant_id',
        'user_id',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:10',
        'cost_at_transaction' => 'decimal:10',
        'metadata' => 'array',
    ];

    // Immutable: Prevent updates or deletes
    public static function boot()
    {
        parent::boot();
        static::updating(function ($model) {
            throw new \Exception("Stock Ledger entries are immutable.");
        });
        static::deleting(function ($model) {
            throw new \Exception("Stock Ledger entries are immutable.");
        });
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function binLocation(): BelongsTo
    {
        return $this->belongsTo(BinLocation::class);
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }
}
