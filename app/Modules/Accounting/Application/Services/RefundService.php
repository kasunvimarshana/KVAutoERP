<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Modules\Accounting\Application\Contracts\RefundServiceInterface;
use Modules\Accounting\Domain\Entities\Refund;
use Modules\Accounting\Domain\RepositoryInterfaces\RefundRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

class RefundService implements RefundServiceInterface
{
    public function __construct(
        private readonly RefundRepositoryInterface $repository,
    ) {}

    public function findById(int $id): Refund
    {
        $refund = $this->repository->findById($id);

        if ($refund === null) {
            throw new NotFoundException('Refund', $id);
        }

        return $refund;
    }

    public function create(array $data): Refund
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Refund
    {
        $refund = $this->repository->update($id, $data);

        if ($refund === null) {
            throw new NotFoundException('Refund', $id);
        }

        return $refund;
    }
}
