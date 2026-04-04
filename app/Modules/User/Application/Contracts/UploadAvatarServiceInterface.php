<?php

declare(strict_types=1);

namespace Modules\User\Application\Contracts;

use Modules\User\Domain\Entities\User;

interface UploadAvatarServiceInterface
{
    public function execute(int $id, string $avatarPath): User;
}
