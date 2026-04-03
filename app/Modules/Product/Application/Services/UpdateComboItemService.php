<?php
declare(strict_types=1);
namespace Modules\Product\Application\Services;
use Modules\Core\Application\Services\BaseService;
use Modules\Product\Application\Contracts\UpdateComboItemServiceInterface;
use Modules\Product\Domain\RepositoryInterfaces\ComboItemRepositoryInterface;
class UpdateComboItemService extends BaseService implements UpdateComboItemServiceInterface
{
    public function __construct(ComboItemRepositoryInterface $repository) { parent::__construct($repository); }
    protected function handle(array $data): mixed { return null; }
}
