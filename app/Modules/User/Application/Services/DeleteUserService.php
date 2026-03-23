<?php

namespace Modules\User\Application\Services;

use Modules\Core\Application\Services\BaseService;
use Modules\User\Domain\RepositoryInterfaces\UserRepositoryInterface;

class DeleteUserService extends BaseService
{
    public function __construct(UserRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function handle(array $data): bool
    {
        $id = $data['id'];
        $user = $this->repository->find($id);
        if (!$user) {
            throw new \RuntimeException('User not found');
        }
        return $this->repository->delete($id);
    }
}
