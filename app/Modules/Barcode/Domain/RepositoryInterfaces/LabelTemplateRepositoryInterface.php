<?php

declare(strict_types=1);

namespace Modules\Barcode\Domain\RepositoryInterfaces;

use Modules\Barcode\Domain\Entities\LabelTemplate;

interface LabelTemplateRepositoryInterface
{
    public function create(array $data): LabelTemplate;

    public function update(int $id, array $data): LabelTemplate;

    public function delete(int $id): void;

    public function findById(int $id, int $tenantId): ?LabelTemplate;

    /** @return LabelTemplate[] */
    public function listAll(int $tenantId): array;
}
