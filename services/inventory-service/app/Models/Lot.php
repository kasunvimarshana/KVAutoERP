<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lot extends Model
{
    use HasFactory;

    protected $fillable = [
        'lot_number',
        'product_id',
        'manufacturing_date',
        'expiry_date',
        'best_before_date',
        'is_quarantined',
        'quarantine_reason',
        'status', // Released, Quarantined, Recalled, Expired
        'tenant_id',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'best_before_date' => 'date',
        'is_quarantined' => 'boolean',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(StockLevel.class);
    }

    /**
     * Check if the lot is expired
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Quarantine a lot
     * 
     * @param string $reason
     * @return bool
     */
    public function quarantine(string $reason): bool
    {
        $this->is_quarantined = true;
        $this->quarantine_reason = $reason;
        $this->status = 'Quarantined';
        return $this->save();
    }
}
