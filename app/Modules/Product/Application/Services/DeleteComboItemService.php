<?php
declare(strict_types=1);
namespace Modules\Product\Application\Services;
use Modules\Core\Application\Services\BaseService;
use Modules\Product\Application\Contracts\DeleteComboItemServiceInterface;
use Modules\Product\Domain\RepositoryInterfaces\ComboItemRepositoryInterface;
class DeleteComboItemService extends BaseService implements DeleteComboItemServiceInterface
{
    public function __construct(ComboItemRepositoryInterface $repository) { parent::__construct($repository); }
    protected function handle(array $data): bool { return $this->repository->delete($data['id']); }
}
