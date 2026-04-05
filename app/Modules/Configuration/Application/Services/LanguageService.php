<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Services;

use Illuminate\Support\Collection;
use Modules\Configuration\Application\Contracts\LanguageServiceInterface;
use Modules\Configuration\Domain\Entities\Language;
use Modules\Configuration\Domain\RepositoryInterfaces\LanguageRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

final class LanguageService implements LanguageServiceInterface
{
    public function __construct(
        private readonly LanguageRepositoryInterface $repository,
    ) {}

    public function findById(int $id): ?Language
    {
        return $this->repository->findById($id);
    }

    public function findByCode(?int $tenantId, string $code): ?Language
    {
        return $this->repository->findByCode($tenantId, $code);
    }

    public function getDefault(?int $tenantId): ?Language
    {
        return $this->repository->findDefault($tenantId);
    }

    public function getActive(?int $tenantId): Collection
    {
        return $this->repository->findActive($tenantId);
    }

    public function create(array $data): Language
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): ?Language
    {
        $language = $this->repository->findById($id);

        if ($language === null) {
            throw new NotFoundException("Language with ID {$id} not found.");
        }

        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        $language = $this->repository->findById($id);

        if ($language === null) {
            throw new NotFoundException("Language with ID {$id} not found.");
        }

        return $this->repository->delete($id);
    }

    public function setDefault(?int $tenantId, int $languageId): Language
    {
        $language = $this->repository->findById($languageId);

        if ($language === null) {
            throw new NotFoundException("Language with ID {$languageId} not found.");
        }

        if ($language->tenantId !== $tenantId) {
            throw new NotFoundException("Language with ID {$languageId} not found.");
        }

        // Unset current default for this tenant scope
        $current = $this->repository->findDefault($tenantId);
        if ($current !== null && $current->id !== $languageId) {
            $this->repository->update($current->id, ['is_default' => false]);
        }

        return $this->repository->update($languageId, ['is_default' => true]);
    }
}
