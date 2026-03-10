<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Order extends Model {
    use HasFactory, HasUuids, SoftDeletes;
    protected $table = 'orders';
    protected $fillable = ['tenant_id','user_id','order_number','status','saga_status','subtotal','tax','discount','total','shipping_address','notes','metadata','confirmed_at','cancelled_at'];
    protected function casts(): array { return ['subtotal'=>'decimal:4','tax'=>'decimal:4','discount'=>'decimal:4','total'=>'decimal:4','shipping_address'=>'array','metadata'=>'array','confirmed_at'=>'datetime','cancelled_at'=>'datetime']; }
    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(OrderItem::class, 'order_id'); }
    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isConfirmed(): bool { return $this->status === 'confirmed'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }
}
