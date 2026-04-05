<?php declare(strict_types=1);
namespace Modules\Currency\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Currency\Domain\Entities\Currency;
use Modules\Currency\Domain\RepositoryInterfaces\CurrencyRepositoryInterface;
use Modules\Currency\Infrastructure\Persistence\Eloquent\Models\CurrencyModel;
class EloquentCurrencyRepository implements CurrencyRepositoryInterface {
    public function __construct(private readonly CurrencyModel $model) {}
    public function findById(int $id): ?Currency {
        $m = $this->model->newQuery()->find($id);
        return $m ? $this->toEntity($m) : null;
    }
    public function findByCode(int $tenantId, string $code): ?Currency {
        $m = $this->model->newQuery()->where('tenant_id',$tenantId)->where('code',$code)->first();
        return $m ? $this->toEntity($m) : null;
    }
    public function findByTenant(int $tenantId): array {
        return $this->model->newQuery()->where('tenant_id',$tenantId)->get()->map(fn($m)=>$this->toEntity($m))->all();
    }
    public function save(Currency $c): Currency {
        $m = $c->getId() ? $this->model->newQuery()->findOrFail($c->getId()) : new CurrencyModel();
        $m->tenant_id=$c->getTenantId(); $m->code=$c->getCode(); $m->name=$c->getName(); $m->symbol=$c->getSymbol();
        $m->decimal_places=$c->getDecimalPlaces(); $m->is_default=$c->isDefault(); $m->is_active=$c->isActive();
        $m->save();
        return $this->toEntity($m);
    }
    public function delete(int $id): void { $this->model->newQuery()->findOrFail($id)->delete(); }
    private function toEntity(CurrencyModel $m): Currency {
        return new Currency($m->id,$m->tenant_id,$m->code,$m->name,$m->symbol,$m->decimal_places,(bool)$m->is_default,(bool)$m->is_active);
    }
}
