<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Application\Services;
use Modules\OrganizationUnit\Application\Contracts\MoveOrganizationUnitServiceInterface;

class MoveOrganizationUnitService implements MoveOrganizationUnitServiceInterface {
    public function __construct(private \Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitRepositoryInterface $repo) {}

    public function execute(array $data = []): mixed {
        return null;
    }
}
