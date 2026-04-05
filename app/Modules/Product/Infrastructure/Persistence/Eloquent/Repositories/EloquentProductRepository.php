<?php declare(strict_types=1);
namespace Modules\Product\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Product\Domain\Entities\Product;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductModel;
class EloquentProductRepository implements ProductRepositoryInterface {
    public function __construct(private readonly ProductModel $model) {}
    public function findById(int $id): ?Product { $m=$this->model->newQuery()->find($id); return $m?$this->toEntity($m):null; }
    public function findBySku(int $tenantId, string $sku): ?Product { $m=$this->model->newQuery()->where('tenant_id',$tenantId)->where('sku',$sku)->first(); return $m?$this->toEntity($m):null; }
    public function findByTenant(int $tenantId): array { return $this->model->newQuery()->where('tenant_id',$tenantId)->get()->map(fn($m)=>$this->toEntity($m))->all(); }
    public function findByType(int $tenantId, string $type): array { return $this->model->newQuery()->where('tenant_id',$tenantId)->where('type',$type)->get()->map(fn($m)=>$this->toEntity($m))->all(); }
    public function save(Product $p): Product {
        $m = $p->getId() ? $this->model->newQuery()->findOrFail($p->getId()) : new ProductModel();
        $m->tenant_id=$p->getTenantId(); $m->sku=$p->getSku(); $m->name=$p->getName(); $m->type=$p->getType();
        $m->category_id=$p->getCategoryId(); $m->cost_price=$p->getCostPrice(); $m->sale_price=$p->getSalePrice();
        $m->currency=$p->getCurrency(); $m->description=$p->getDescription(); $m->is_active=$p->isActive();
        $m->is_taxable=$p->isTaxable(); $m->tax_group_id=$p->getTaxGroupId(); $m->barcode=$p->getBarcode(); $m->unit=$p->getUnit();
        $m->save();
        return $this->toEntity($m);
    }
    public function delete(int $id): void { $this->model->newQuery()->findOrFail($id)->delete(); }
    private function toEntity(ProductModel $m): Product {
        return new Product($m->id,$m->tenant_id,$m->sku,$m->name,$m->type,$m->category_id,(float)$m->cost_price,(float)$m->sale_price,$m->currency,$m->description,(bool)$m->is_active,(bool)$m->is_taxable,$m->tax_group_id,$m->barcode,$m->unit);
    }
}
