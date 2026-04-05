<?php declare(strict_types=1);
namespace Modules\POS\Domain\RepositoryInterfaces;
use Modules\POS\Domain\Entities\Terminal;
interface TerminalRepositoryInterface {
    public function findById(int $id): ?Terminal;
    public function findByTenant(int $tenantId): array;
    public function save(Terminal $terminal): Terminal;
}
