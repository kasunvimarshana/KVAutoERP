<?php

declare(strict_types=1);

namespace Modules\Barcode\Application\Contracts;

use Modules\Barcode\Domain\Entities\LabelTemplate;

interface LabelTemplateServiceInterface
{
    public function create(array $data): LabelTemplate;

    public function update(int $id, array $data): LabelTemplate;

    public function delete(int $id): void;

    public function findById(int $id, int $tenantId): ?LabelTemplate;

    public function render(int $id, array $data, int $tenantId): string;
}
