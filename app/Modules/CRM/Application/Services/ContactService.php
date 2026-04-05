<?php

declare(strict_types=1);

namespace Modules\CRM\Application\Services;

use Modules\CRM\Application\Contracts\ContactServiceInterface;
use Modules\CRM\Domain\Entities\Contact;
use Modules\CRM\Domain\RepositoryInterfaces\ContactRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

class ContactService implements ContactServiceInterface
{
    public function __construct(
        private readonly ContactRepositoryInterface $repository,
    ) {}

    public function create(array $data): Contact
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Contact
    {
        $contact = $this->repository->update($id, $data);

        if ($contact === null) {
            throw new NotFoundException('Contact', $id);
        }

        return $contact;
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function find(int $id): Contact
    {
        $contact = $this->repository->findById($id);

        if ($contact === null) {
            throw new NotFoundException('Contact', $id);
        }

        return $contact;
    }

    public function findByEmail(int $tenantId, string $email): Contact
    {
        $contact = $this->repository->findByEmail($tenantId, $email);

        if ($contact === null) {
            throw new NotFoundException("Contact with email '{$email}'");
        }

        return $contact;
    }

    public function findByType(int $tenantId, string $type): array
    {
        return $this->repository->findByType($tenantId, $type);
    }

    public function search(int $tenantId, string $query): array
    {
        return $this->repository->search($tenantId, $query);
    }
}
