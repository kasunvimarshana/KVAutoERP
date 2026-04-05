<?php

declare(strict_types=1);

namespace Modules\CRM\Application\Contracts;

use Modules\CRM\Domain\Entities\Contact;

interface ContactServiceInterface
{
    public function create(array $data): Contact;

    public function update(int $id, array $data): Contact;

    public function delete(int $id): bool;

    public function find(int $id): Contact;

    public function findByEmail(int $tenantId, string $email): Contact;

    /** @return Contact[] */
    public function findByType(int $tenantId, string $type): array;

    /** @return Contact[] */
    public function search(int $tenantId, string $query): array;
}
