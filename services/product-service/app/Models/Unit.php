<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'type', // Length, Weight, Area, Volume, Time, Count
        'is_base',
        'conversion_factor', // relative to base unit of the same type
        'base_unit_id',
    ];

    protected $casts = [
        'is_base' => 'boolean',
        'conversion_factor' => 'decimal:10',
    ];

    public function conversions(): HasMany
    {
        return $this->hasMany(UnitConversion::class, 'from_unit_id');
    }

    /**
     * Convert value from this unit to another unit
     *
     * @param float $value
     * @param Unit $toUnit
     * @return float
     */
    public function convertTo(float $value, Unit $toUnit): float
    {
        if ($this->id === $toUnit->id) return $value;

        // If units are of different types, they are not convertible directly
        if ($this->type !== $toUnit->type) {
            throw new \Exception("Cannot convert between different unit types: {$this->type} to {$toUnit->type}");
        }

        // Standard conversion relative to base unit: value * from_factor / to_factor
        return ($value * $this->conversion_factor) / $toUnit->conversion_factor;
    }

    /**
     * Get a list of all convertible units for a specific type
     */
    public static function getConvertibleUnits(string $type)
    {
        return self::where('type', $type)->get();
    }
}
