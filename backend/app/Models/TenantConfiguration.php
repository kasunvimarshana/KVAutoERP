<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantConfiguration extends Model
{
    protected $fillable = [
        'tenant_id',
        'key',
        'value',
        'type',
        'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    protected $hidden = ['value'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /** Decrypt value on the fly when it is marked encrypted. */
    public function getValueAttribute(mixed $value): mixed
    {
        if ($this->is_encrypted && $value !== null) {
            try {
                return decrypt($value);
            } catch (\Exception) {
                return $value;
            }
        }

        return $value;
    }
}
