<?php

namespace App\Models;

use App\Core\Tenant\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'order_number',
        'status',
        'total_amount',
        'currency',
        'notes',
        'saga_id',
        'metadata',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'metadata'     => 'array',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
