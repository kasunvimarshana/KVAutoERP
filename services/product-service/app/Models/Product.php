<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'tenant_id', 'category_id', 'name', 'code', 'sku',
        'description', 'price', 'cost', 'unit', 'status', 'attributes',
    ];

    protected function casts(): array
    {
        return [
            'price'      => 'decimal:4',
            'cost'       => 'decimal:4',
            'attributes' => 'array',
        ];
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function scopeForTenant($query, string $tenantId) { return $query->where('tenant_id', $tenantId); }
    public function scopeActive($query) { return $query->where('status', 'active'); }
}
