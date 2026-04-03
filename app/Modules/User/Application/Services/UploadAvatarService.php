<?php
declare(strict_types=1);
namespace Modules\User\Application\Services;
use Modules\Core\Application\Contracts\FileStorageServiceInterface;
use Modules\User\Application\Contracts\UploadAvatarServiceInterface;
use Modules\User\Domain\RepositoryInterfaces\UserRepositoryInterface;

class UploadAvatarService implements UploadAvatarServiceInterface {
    public function __construct(
        private UserRepositoryInterface $users,
        private FileStorageServiceInterface $storage
    ) {}

    public function execute(array $data = []): mixed {
        return null;
    }
}
