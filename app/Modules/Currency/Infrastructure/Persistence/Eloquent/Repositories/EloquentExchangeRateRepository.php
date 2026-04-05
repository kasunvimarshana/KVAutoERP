<?php declare(strict_types=1);
namespace Modules\Currency\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Currency\Domain\Entities\ExchangeRate;
use Modules\Currency\Domain\RepositoryInterfaces\ExchangeRateRepositoryInterface;
use Modules\Currency\Infrastructure\Persistence\Eloquent\Models\ExchangeRateModel;
class EloquentExchangeRateRepository implements ExchangeRateRepositoryInterface {
    public function __construct(private readonly ExchangeRateModel $model) {}
    public function findLatest(int $tenantId, string $from, string $to): ?ExchangeRate {
        $m = $this->model->newQuery()->where('tenant_id',$tenantId)->where('from_currency',$from)->where('to_currency',$to)->orderByDesc('effective_date')->first();
        return $m ? $this->toEntity($m) : null;
    }
    public function save(ExchangeRate $r): ExchangeRate {
        $m = $r->getId() ? $this->model->newQuery()->findOrFail($r->getId()) : new ExchangeRateModel();
        $m->tenant_id=$r->getTenantId(); $m->from_currency=$r->getFromCurrency(); $m->to_currency=$r->getToCurrency();
        $m->rate=$r->getRate(); $m->effective_date=$r->getEffectiveDate()->format('Y-m-d');
        $m->save();
        return $this->toEntity($m);
    }
    private function toEntity(ExchangeRateModel $m): ExchangeRate {
        return new ExchangeRate($m->id,$m->tenant_id,$m->from_currency,$m->to_currency,(float)$m->rate,new \DateTimeImmutable($m->effective_date->toDateString()));
    }
}
