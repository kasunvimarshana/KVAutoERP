<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Modules\Configuration\Application\Contracts\GetSettingServiceInterface;
use Modules\Configuration\Domain\Entities\Setting;
use Modules\Configuration\Domain\Exceptions\SettingNotFoundException;
use Modules\Configuration\Domain\RepositoryInterfaces\SettingRepositoryInterface;

class GetSettingService implements GetSettingServiceInterface
{
    public function __construct(
        private readonly SettingRepositoryInterface $repository,
    ) {}

    public function execute(int $tenantId, string $group, string $key): Setting
    {
        $setting = $this->repository->get($tenantId, $group, $key);

        if ($setting === null) {
            throw new SettingNotFoundException($group, $key);
        }

        return $setting;
    }
}
