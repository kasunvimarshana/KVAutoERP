<?php
declare(strict_types=1);
namespace Modules\CRM\Application\Contracts;
use Modules\CRM\Domain\Entities\Contact;
use Illuminate\Support\Collection;
interface ContactServiceInterface {
    public function create(array $data): Contact;
    public function update(int $id, array $data): Contact;
    public function delete(int $id): bool;
    public function findById(int $id): ?Contact;
    public function findByTenant(int $tenantId): Collection;
    public function findByType(int $tenantId, string $type): Collection;
    public function findByEmail(string $email): ?Contact;
}
