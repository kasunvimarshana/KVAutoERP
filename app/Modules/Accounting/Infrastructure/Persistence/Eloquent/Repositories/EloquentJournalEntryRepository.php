<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Domain\Entities\JournalEntry;
use Modules\Accounting\Domain\Entities\JournalLine;
use Modules\Accounting\Domain\RepositoryInterfaces\JournalEntryRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\JournalEntryModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\JournalLineModel;
use Modules\Core\Domain\Exceptions\NotFoundException;

class EloquentJournalEntryRepository implements JournalEntryRepositoryInterface
{
    public function __construct(
        private readonly JournalEntryModel $model,
        private readonly JournalLineModel $lineModel,
    ) {}

    public function findById(string $id): ?JournalEntry
    {
        $m = $this->model->withoutGlobalScopes()->find($id);
        return $m ? $this->toEntity($m) : null;
    }

    public function allByTenant(string $tenantId): Collection
    {
        return $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)->orderByDesc('date')->get()
            ->map(fn (JournalEntryModel $m) => $this->toEntity($m));
    }

    public function create(array $data, array $lines): JournalEntry
    {
        return DB::transaction(function () use ($data, $lines) {
            $entry = $this->model->create($data);
            foreach ($lines as $line) {
                $this->lineModel->create(array_merge($line, [
                    'journal_entry_id' => $entry->id,
                    'tenant_id'        => $data['tenant_id'],
                ]));
            }
            return $this->toEntity($entry->fresh());
        });
    }

    public function update(string $id, array $data): JournalEntry
    {
        $m = $this->model->withoutGlobalScopes()->findOrFail($id);
        $m->update($data);
        return $this->toEntity($m->fresh());
    }

    public function updateStatus(string $id, string $status): JournalEntry
    {
        $m = $this->model->withoutGlobalScopes()->findOrFail($id);
        $m->update(['status' => $status]);
        return $this->toEntity($m->fresh());
    }

    public function delete(string $id): bool
    {
        $m = $this->model->withoutGlobalScopes()->find($id);
        if (! $m) { throw new NotFoundException('JournalEntry', $id); }
        return (bool) $m->delete();
    }

    public function getLines(string $journalEntryId): Collection
    {
        return $this->lineModel->where('journal_entry_id', $journalEntryId)->get()
            ->map(fn (JournalLineModel $l) => new JournalLine(
                id: $l->id, journalEntryId: $l->journal_entry_id,
                accountId: $l->account_id, debit: (float)$l->debit,
                credit: (float)$l->credit, description: $l->description,
                tenantId: $l->tenant_id,
            ));
    }

    public function nextEntryNumber(string $tenantId): string
    {
        $count = $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)->count();
        return 'JE-'.str_pad((string)($count + 1), 6, '0', STR_PAD_LEFT);
    }

    private function toEntity(JournalEntryModel $m): JournalEntry
    {
        return new JournalEntry(
            id: $m->id, tenantId: $m->tenant_id, entryNumber: $m->entry_number,
            date: new \DateTimeImmutable($m->date->toDateString()),
            description: $m->description, reference: $m->reference,
            status: $m->status, totalDebit: (float)$m->total_debit,
            totalCredit: (float)$m->total_credit, createdBy: $m->created_by,
        );
    }
}
