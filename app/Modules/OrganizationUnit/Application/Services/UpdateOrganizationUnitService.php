<?php

declare(strict_types=1);

namespace Modules\OrganizationUnit\Application\Services;

use Modules\Core\Application\Services\BaseService;
use Modules\Core\Domain\ValueObjects\Code;
use Modules\Core\Domain\ValueObjects\Metadata;
use Modules\Core\Domain\ValueObjects\Name;
use Modules\OrganizationUnit\Application\Contracts\UpdateOrganizationUnitServiceInterface;
use Modules\OrganizationUnit\Domain\Entities\OrganizationUnit;
use Modules\OrganizationUnit\Domain\Events\OrganizationUnitUpdated;
use Modules\OrganizationUnit\Domain\Exceptions\OrganizationUnitNotFoundException;
use Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitRepositoryInterface;

class UpdateOrganizationUnitService extends BaseService implements UpdateOrganizationUnitServiceInterface
{
    private OrganizationUnitRepositoryInterface $orgUnitRepository;

    public function __construct(OrganizationUnitRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->orgUnitRepository = $repository;
    }

    protected function handle(array $data): OrganizationUnit
    {
        $id   = (int) $data['id'];
        $unit = $this->orgUnitRepository->find($id);
        if (! $unit) {
            throw new OrganizationUnitNotFoundException($id);
        }

        // Use existing entity values as defaults so partial updates are safe.
        $name = array_key_exists('name', $data)
            ? new Name((string) $data['name'])
            : $unit->getName();

        $code = array_key_exists('code', $data)
            ? ($data['code'] !== null ? new Code((string) $data['code']) : null)
            : $unit->getCode();

        $description = array_key_exists('description', $data)
            ? $data['description']
            : $unit->getDescription();

        $metadata = array_key_exists('metadata', $data)
            ? (is_array($data['metadata']) ? new Metadata($data['metadata']) : null)
            : $unit->getMetadata();

        $unit->updateDetails($name, $code, $description, $metadata);

        // Only move the node when parent_id is explicitly supplied and differs.
        if (array_key_exists('parent_id', $data) && $data['parent_id'] !== $unit->getParentId()) {
            $this->orgUnitRepository->moveNode($id, $data['parent_id']);
        }

        $saved = $this->orgUnitRepository->save($unit);
        $this->addEvent(new OrganizationUnitUpdated($saved));

        return $saved;
    }
}
