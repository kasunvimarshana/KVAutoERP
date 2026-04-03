<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Application\Services;
use Modules\Core\Domain\ValueObjects\Code;
use Modules\Core\Domain\ValueObjects\Metadata;
use Modules\Core\Domain\ValueObjects\Name;
use Modules\OrganizationUnit\Application\Contracts\UpdateOrganizationUnitServiceInterface;
use Modules\OrganizationUnit\Application\DTOs\UpdateOrganizationUnitData;
use Modules\OrganizationUnit\Domain\Exceptions\OrganizationUnitNotFoundException;
use Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitRepositoryInterface;

class UpdateOrganizationUnitService implements UpdateOrganizationUnitServiceInterface {
    public function __construct(private OrganizationUnitRepositoryInterface $repo) {}

    public function execute(array $data = []): mixed {
        return $this->handle($data);
    }

    protected function handle(array $data): mixed {
        $dto = UpdateOrganizationUnitData::fromArray($data);

        $unit = $this->repo->find((int)$data['id']);
        if (!$unit) {
            throw new OrganizationUnitNotFoundException($data['id']);
        }

        $hasDetailChanges = $dto->isProvided('name')
            || $dto->isProvided('code')
            || $dto->isProvided('description')
            || $dto->isProvided('metadata');

        if ($hasDetailChanges) {
            $name = $dto->isProvided('name')
                ? new Name($dto->name)
                : $unit->getName();

            $code = $dto->isProvided('code')
                ? ($dto->code !== null ? new Code($dto->code) : null)
                : $unit->getCode();

            $description = $dto->isProvided('description')
                ? $dto->description
                : $unit->getDescription();

            $metadata = $dto->isProvided('metadata')
                ? new Metadata($dto->metadata ?? [])
                : $unit->getMetadata();

            $unit->updateDetails($name, $code, $description, $metadata);
        }

        if ($dto->isProvided('parent_id')) {
            $unit->moveTo((int)$dto->parent_id);
            $this->repo->moveNode($unit);
        }

        return $this->repo->save($unit);
    }
}
