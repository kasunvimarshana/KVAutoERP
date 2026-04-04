<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\Configuration\Application\Contracts\SetSettingServiceInterface;
use Modules\Configuration\Application\DTOs\SetSettingData;
use Modules\Configuration\Domain\Entities\Setting;
use Modules\Configuration\Domain\Events\SettingUpdated;
use Modules\Configuration\Domain\RepositoryInterfaces\SettingRepositoryInterface;

class SetSettingService implements SetSettingServiceInterface
{
    public function __construct(
        private readonly SettingRepositoryInterface $repository,
    ) {}

    public function execute(SetSettingData $data): Setting
    {
        $value = is_array($data->value) || is_object($data->value)
            ? json_encode($data->value)
            : ($data->value !== null ? (string) $data->value : null);

        $setting = $this->repository->set(
            $data->tenantId,
            $data->group,
            $data->key,
            $value,
            $data->type,
        );

        Event::dispatch(new SettingUpdated($data->tenantId, $data->group, $data->key));

        return $setting;
    }
}
