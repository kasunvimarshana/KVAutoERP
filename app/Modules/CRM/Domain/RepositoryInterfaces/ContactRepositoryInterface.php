<?php

declare(strict_types=1);

namespace Modules\CRM\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\CRM\Domain\Entities\Contact;

interface ContactRepositoryInterface
{
    public function findById(int $id): ?Contact;
    public function findByTenant(int $tenantId): Collection;
    public function findByType(int $tenantId, string $type): Collection;
    public function findByEmail(int $tenantId, string $email): ?Contact;
    public function save(array $data): Contact;
    public function update(int $id, array $data): Contact;
    public function delete(int $id): void;
}
