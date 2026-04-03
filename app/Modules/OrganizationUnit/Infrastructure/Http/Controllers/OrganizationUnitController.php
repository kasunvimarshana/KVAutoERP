<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Infrastructure\Http\Controllers;
use Modules\OrganizationUnit\Application\Contracts\CreateOrganizationUnitServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\DeleteOrganizationUnitServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\FindOrganizationUnitServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\MoveOrganizationUnitServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\UpdateOrganizationUnitServiceInterface;
use Modules\OrganizationUnit\Application\DTOs\OrganizationUnitData;
use Modules\OrganizationUnit\Application\DTOs\UpdateOrganizationUnitData;

class OrganizationUnitController {
    public function __construct(
        private FindOrganizationUnitServiceInterface $finder,
        private CreateOrganizationUnitServiceInterface $creator,
        private UpdateOrganizationUnitServiceInterface $updater,
        private DeleteOrganizationUnitServiceInterface $deleter,
        private MoveOrganizationUnitServiceInterface $mover
    ) {}

    public function index() {}
    public function show($id) {}
    public function store() {}
    public function update($id) {}
    public function destroy($id) {}
    public function descendants($id) {}
    public function ancestors($id) {}
}
