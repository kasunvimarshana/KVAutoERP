<?php

declare(strict_types=1);

namespace Modules\CRM\Application\Services;

use Modules\CRM\Application\Contracts\ActivityServiceInterface;
use Modules\CRM\Domain\Entities\Activity;
use Modules\CRM\Domain\RepositoryInterfaces\ActivityRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

class ActivityService implements ActivityServiceInterface
{
    public function __construct(
        private readonly ActivityRepositoryInterface $repository,
    ) {}

    public function create(array $data): Activity
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Activity
    {
        $activity = $this->repository->update($id, $data);

        if ($activity === null) {
            throw new NotFoundException('Activity', $id);
        }

        return $activity;
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function find(int $id): Activity
    {
        $activity = $this->repository->findById($id);

        if ($activity === null) {
            throw new NotFoundException('Activity', $id);
        }

        return $activity;
    }

    public function complete(int $id): Activity
    {
        return $this->update($id, [
            'status'       => 'completed',
            'completed_at' => now()->toDateTimeString(),
        ]);
    }

    public function cancel(int $id): Activity
    {
        return $this->update($id, ['status' => 'cancelled']);
    }
}
