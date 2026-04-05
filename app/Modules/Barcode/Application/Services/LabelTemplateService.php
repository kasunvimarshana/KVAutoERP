<?php

declare(strict_types=1);

namespace Modules\Barcode\Application\Services;

use Modules\Barcode\Application\Contracts\LabelTemplateServiceInterface;
use Modules\Barcode\Domain\Entities\LabelTemplate;
use Modules\Barcode\Domain\RepositoryInterfaces\LabelTemplateRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

class LabelTemplateService implements LabelTemplateServiceInterface
{
    public function __construct(
        private readonly LabelTemplateRepositoryInterface $repository,
    ) {}

    public function create(array $data): LabelTemplate
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): LabelTemplate
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }

    public function findById(int $id, int $tenantId): ?LabelTemplate
    {
        return $this->repository->findById($id, $tenantId);
    }

    public function render(int $id, array $data, int $tenantId): string
    {
        $template = $this->repository->findById($id, $tenantId);

        if ($template === null) {
            throw new NotFoundException("LabelTemplate #{$id} not found.");
        }

        return $template->render($data);
    }
}
