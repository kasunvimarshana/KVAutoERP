<?php
declare(strict_types=1);
namespace Modules\User\Application\Contracts;
use Modules\User\Domain\Entities\User;

interface FindUserServiceInterface {
    public function find(int $id): ?User;
    public function findByEmail(string $email): ?User;
}
