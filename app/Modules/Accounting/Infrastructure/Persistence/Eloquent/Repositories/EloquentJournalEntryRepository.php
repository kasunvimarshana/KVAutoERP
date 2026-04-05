<?php declare(strict_types=1);
namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Accounting\Domain\Entities\JournalEntry;
use Modules\Accounting\Domain\Entities\JournalLine;
use Modules\Accounting\Domain\RepositoryInterfaces\JournalEntryRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\JournalEntryModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\JournalLineModel;
class EloquentJournalEntryRepository implements JournalEntryRepositoryInterface {
    public function __construct(private readonly JournalEntryModel $model, private readonly JournalLineModel $lineModel) {}
    public function findById(int $id): ?JournalEntry {
        $m = $this->model->newQuery()->find($id);
        if (!$m) return null;
        $lines = $this->findLinesByEntry($id);
        $entry = $this->toEntity($m);
        $entry->setLines($lines);
        return $entry;
    }
    public function save(JournalEntry $e): JournalEntry {
        $m = $e->getId() ? $this->model->newQuery()->findOrFail($e->getId()) : new JournalEntryModel();
        $m->tenant_id=$e->getTenantId(); $m->reference=$e->getReference(); $m->description=$e->getDescription();
        $m->transaction_date=$e->getTransactionDate()->format('Y-m-d'); $m->status=$e->getStatus();
        $m->currency=$e->getCurrency(); $m->created_by=$e->getCreatedBy(); $m->posted_at=$e->getPostedAt()?->format('Y-m-d H:i:s');
        $m->save();
        return $this->toEntity($m);
    }
    public function saveLine(JournalLine $l): JournalLine {
        $m = $l->getId() ? $this->lineModel->newQuery()->findOrFail($l->getId()) : new JournalLineModel();
        $m->journal_entry_id=$l->getJournalEntryId(); $m->account_id=$l->getAccountId();
        $m->debit_amount=$l->getDebitAmount(); $m->credit_amount=$l->getCreditAmount(); $m->description=$l->getDescription();
        $m->save();
        return new JournalLine($m->id,$m->journal_entry_id,$m->account_id,(float)$m->debit_amount,(float)$m->credit_amount,$m->description);
    }
    public function findLinesByEntry(int $entryId): array {
        return $this->lineModel->newQuery()->where('journal_entry_id',$entryId)->get()->map(fn($m)=>new JournalLine($m->id,$m->journal_entry_id,$m->account_id,(float)$m->debit_amount,(float)$m->credit_amount,$m->description))->all();
    }
    public function delete(int $id): void { $this->model->newQuery()->findOrFail($id)->delete(); }
    private function toEntity(JournalEntryModel $m): JournalEntry {
        return new JournalEntry($m->id,$m->tenant_id,$m->reference,$m->description,new \DateTimeImmutable($m->transaction_date->toDateString()),$m->status,$m->currency,$m->created_by,$m->posted_at ? new \DateTimeImmutable($m->posted_at->toDateTimeString()) : null);
    }
}
