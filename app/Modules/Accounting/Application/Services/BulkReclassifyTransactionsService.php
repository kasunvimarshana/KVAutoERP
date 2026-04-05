<?php declare(strict_types=1);
namespace Modules\Accounting\Application\Services;
use Modules\Accounting\Domain\Entities\BankTransaction;
use Modules\Accounting\Domain\RepositoryInterfaces\BankTransactionRepositoryInterface;
class BulkReclassifyTransactionsService {
    public function __construct(private readonly BankTransactionRepositoryInterface $repo) {}
    public function reclassify(array $transactionIds, int $newAccountId): int {
        $count = 0;
        foreach ($transactionIds as $id) {
            $txn = $this->repo->findById($id);
            if (!$txn) continue;
            $reclassified = new BankTransaction($txn->getId(),$txn->getBankAccountId(),$txn->getTenantId(),$txn->getType(),$txn->getAmount(),$txn->getTransactionDate(),$txn->getDescription(),'categorized',$txn->getSource(),$newAccountId,$txn->getReference());
            $this->repo->save($reclassified);
            $count++;
        }
        return $count;
    }
}
