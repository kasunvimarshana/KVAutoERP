<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Configuration\Application\Contracts\UpdateSystemConfigServiceInterface;
use Modules\Configuration\Application\DTOs\UpdateSystemConfigData;
use Modules\Configuration\Domain\Entities\SystemConfig;
use Modules\Configuration\Domain\Events\SystemConfigUpdated;
use Modules\Configuration\Domain\Repositories\SystemConfigRepositoryInterface;

class UpdateSystemConfigService implements UpdateSystemConfigServiceInterface
{
    public function __construct(
        private readonly SystemConfigRepositoryInterface $repository,
    ) {}

    public function execute(UpdateSystemConfigData $data): SystemConfig
    {
        return DB::transaction(function () use ($data): SystemConfig {
            $config = $this->repository->upsert(
                $data->key,
                $data->value,
                $data->tenantId,
                $data->group,
            );

            Event::dispatch(new SystemConfigUpdated($config));

            return $config;
        });
    }
}
