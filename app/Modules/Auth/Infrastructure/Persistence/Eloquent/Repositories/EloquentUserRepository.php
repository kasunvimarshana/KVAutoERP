<?php declare(strict_types=1);
namespace Modules\Auth\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Auth\Domain\Entities\User;
use Modules\Auth\Domain\RepositoryInterfaces\UserRepositoryInterface;
use Modules\Auth\Infrastructure\Persistence\Eloquent\Models\UserModel;
class EloquentUserRepository implements UserRepositoryInterface {
    public function __construct(private readonly UserModel $model) {}
    public function findById(int $id): ?User {
        $m = $this->model->newQuery()->find($id);
        return $m ? $this->toEntity($m) : null;
    }
    public function findByEmail(string $email): ?User {
        $m = $this->model->newQuery()->where('email',$email)->first();
        return $m ? $this->toEntity($m) : null;
    }
    public function save(User $user): User {
        if ($user->getId()) {
            $m = $this->model->newQuery()->findOrFail($user->getId());
        } else {
            $m = new UserModel();
        }
        $m->tenant_id = $user->getTenantId();
        $m->name = $user->getName();
        $m->email = $user->getEmail();
        $m->password = $user->getPasswordHash();
        $m->role = $user->getRole();
        $m->is_active = $user->isActive();
        $m->save();
        return $this->toEntity($m);
    }
    public function delete(int $id): void {
        $this->model->newQuery()->findOrFail($id)->delete();
    }
    private function toEntity(UserModel $m): User {
        return new User($m->id,$m->tenant_id,$m->name,$m->email,$m->password,$m->role,(bool)$m->is_active,$m->email_verified_at ? new \DateTimeImmutable($m->email_verified_at->toDateTimeString()) : null,$m->created_at ? new \DateTimeImmutable($m->created_at->toDateTimeString()) : null);
    }
}
