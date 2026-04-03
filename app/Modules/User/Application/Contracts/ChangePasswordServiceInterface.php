<?php
declare(strict_types=1);
namespace Modules\User\Application\Contracts;

interface ChangePasswordServiceInterface {
    public function execute(array $data = []): mixed;
}
