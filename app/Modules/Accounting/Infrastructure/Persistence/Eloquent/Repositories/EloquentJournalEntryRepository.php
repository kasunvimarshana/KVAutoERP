<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Accounting\Domain\Entities\JournalEntry;
use Modules\Accounting\Domain\Entities\JournalLine;
use Modules\Accounting\Domain\RepositoryInterfaces\JournalEntryRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\JournalEntryModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\JournalLineModel;

class EloquentJournalEntryRepository implements JournalEntryRepositoryInterface
{
    public function __construct(
        private readonly JournalEntryModel $model,
        private readonly JournalLineModel $lineModel,
    ) {}

    public function findById(int $id): ?JournalEntry
    {
        $record = $this->model->newQueryWithoutScopes()
            ->with('lines')
            ->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByReference(int $tenantId, string $referenceNo): ?JournalEntry
    {
        $record = $this->model->newQueryWithoutScopes()
            ->with('lines')
            ->where('tenant_id', $tenantId)
            ->where('reference_no', $referenceNo)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function create(array $data, array $lines): JournalEntry
    {
        $record = $this->model->newQuery()->create(array_diff_key($data, ['lines' => true]));

        foreach ($lines as $line) {
            $this->lineModel->newQuery()->create(array_merge($line, [
                'journal_entry_id' => $record->id,
            ]));
        }

        return $this->toEntity($record->fresh()->load('lines'));
    }

    public function update(int $id, array $data): ?JournalEntry
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->fill($data)->save();

        return $this->toEntity($record->fresh()->load('lines'));
    }

    public function findByDateRange(int $tenantId, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return $this->model->newQueryWithoutScopes()
            ->with('lines')
            ->where('tenant_id', $tenantId)
            ->whereBetween('entry_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->get()
            ->map(fn (JournalEntryModel $m) => $this->toEntity($m))
            ->all();
    }

    public function post(int $id): bool
    {
        return (bool) $this->model->newQueryWithoutScopes()
            ->where('id', $id)
            ->update(['status' => 'posted']);
    }

    public function reverse(int $id): JournalEntry
    {
        $original = $this->model->newQueryWithoutScopes()->with('lines')->findOrFail($id);

        $reversed = $this->model->newQuery()->create([
            'tenant_id'    => $original->tenant_id,
            'reference_no' => 'REV-' . $original->reference_no,
            'description'  => 'Reversal of: ' . $original->description,
            'entry_date'   => now()->toDateString(),
            'status'       => 'draft',
            'created_by'   => $original->created_by,
        ]);

        foreach ($original->lines as $line) {
            $this->lineModel->newQuery()->create([
                'journal_entry_id' => $reversed->id,
                'account_id'       => $line->account_id,
                'description'      => $line->description,
                'debit'            => $line->credit,
                'credit'           => $line->debit,
                'metadata'         => $line->metadata,
            ]);
        }

        return $this->toEntity($reversed->fresh()->load('lines'));
    }

    private function toEntity(JournalEntryModel $model): JournalEntry
    {
        $lines = $model->relationLoaded('lines')
            ? $model->lines->map(fn (JournalLineModel $l) => $this->toLineEntity($l))->all()
            : [];

        return new JournalEntry(
            id: $model->id,
            tenantId: $model->tenant_id,
            referenceNo: $model->reference_no,
            description: $model->description,
            entryDate: $model->entry_date,
            status: $model->status,
            lines: $lines,
            createdBy: $model->created_by,
            createdAt: $model->created_at,
        );
    }

    private function toLineEntity(JournalLineModel $model): JournalLine
    {
        return new JournalLine(
            id: $model->id,
            journalEntryId: $model->journal_entry_id,
            accountId: $model->account_id,
            description: $model->description,
            debit: (float) $model->debit,
            credit: (float) $model->credit,
            metadata: $model->metadata ?? [],
        );
    }
}
