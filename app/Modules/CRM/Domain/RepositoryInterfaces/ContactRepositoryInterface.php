<?php declare(strict_types=1);
namespace Modules\CRM\Domain\RepositoryInterfaces;
use Modules\CRM\Domain\Entities\Contact;
interface ContactRepositoryInterface {
    public function findById(int $id): ?Contact;
    public function findByTenant(int $tenantId, ?string $type = null): array;
    public function save(Contact $contact): Contact;
    public function delete(int $id): void;
}
