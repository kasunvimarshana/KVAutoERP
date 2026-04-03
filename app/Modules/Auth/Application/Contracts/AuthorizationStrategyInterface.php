<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Contracts;

interface AuthorizationStrategyInterface {
    public function getName(): string;
    public function can(int $userId, string $ability, mixed $subject = null): bool;
}
