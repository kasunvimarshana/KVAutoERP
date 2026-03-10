<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class OrderItem extends Model {
    use HasFactory, HasUuids;
    protected $table = 'order_items';
    protected $fillable = ['order_id','product_id','product_code','product_name','quantity','unit_price','discount','total'];
    protected function casts(): array { return ['quantity'=>'integer','unit_price'=>'decimal:4','discount'=>'decimal:4','total'=>'decimal:4']; }
    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(Order::class, 'order_id'); }
}
