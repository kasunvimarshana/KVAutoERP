<?php declare(strict_types=1);
namespace Modules\Tenant\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use Modules\Tenant\Infrastructure\Persistence\Eloquent\Models\TenantModel;
class EloquentTenantRepository implements TenantRepositoryInterface {
    public function __construct(private readonly TenantModel $model) {}
    public function findById(int $id): ?Tenant {
        $m = $this->model->newQuery()->find($id);
        return $m ? $this->toEntity($m) : null;
    }
    public function findBySlug(string $slug): ?Tenant {
        $m = $this->model->newQuery()->where('slug',$slug)->first();
        return $m ? $this->toEntity($m) : null;
    }
    public function save(Tenant $tenant): Tenant {
        if ($tenant->getId()) { $m = $this->model->newQuery()->findOrFail($tenant->getId()); } else { $m = new TenantModel(); }
        $m->name = $tenant->getName(); $m->slug = $tenant->getSlug(); $m->plan = $tenant->getPlan(); $m->is_active = $tenant->isActive(); $m->settings = $tenant->getSettings();
        $m->save();
        return $this->toEntity($m);
    }
    public function delete(int $id): void { $this->model->newQuery()->findOrFail($id)->delete(); }
    private function toEntity(TenantModel $m): Tenant {
        return new Tenant($m->id,$m->name,$m->slug,$m->plan,(bool)$m->is_active,$m->settings,$m->trial_ends_at ? new \DateTimeImmutable($m->trial_ends_at->toDateTimeString()) : null,$m->created_at ? new \DateTimeImmutable($m->created_at->toDateTimeString()) : null);
    }
}
