<?php
declare(strict_types=1);
namespace Modules\User\Domain\Events;
use Modules\User\Domain\Entities\User;

class UserPasswordChanged {
    public function __construct(public readonly User $user) {}
}
