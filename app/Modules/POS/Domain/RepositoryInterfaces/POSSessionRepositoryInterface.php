<?php declare(strict_types=1);
namespace Modules\POS\Domain\RepositoryInterfaces;
use Modules\POS\Domain\Entities\POSSession;
interface POSSessionRepositoryInterface {
    public function findById(int $id): ?POSSession;
    public function findOpenByTerminal(int $terminalId): ?POSSession;
    public function save(POSSession $session): POSSession;
}
