<?php

declare(strict_types=1);

namespace Modules\CRM\Domain\RepositoryInterfaces;

use Modules\CRM\Domain\Entities\Contact;

interface ContactRepositoryInterface
{
    public function findById(int $id): ?Contact;

    public function findByEmail(int $tenantId, string $email): ?Contact;

    /** @return Contact[] */
    public function findByType(int $tenantId, string $type): array;

    public function create(array $data): Contact;

    public function update(int $id, array $data): ?Contact;

    public function delete(int $id): bool;

    /** @return Contact[] */
    public function search(int $tenantId, string $query): array;
}
