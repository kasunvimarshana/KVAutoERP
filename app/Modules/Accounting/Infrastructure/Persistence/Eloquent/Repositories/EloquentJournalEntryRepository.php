<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\JournalEntry;
use Modules\Accounting\Domain\Entities\JournalLine;
use Modules\Accounting\Domain\RepositoryInterfaces\JournalEntryRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\JournalEntryModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\JournalLineModel;

final class EloquentJournalEntryRepository implements JournalEntryRepositoryInterface
{
    public function __construct(
        private readonly JournalEntryModel $model,
        private readonly JournalLineModel $lineModel,
    ) {}

    public function findById(int $id): ?JournalEntry
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByReference(int $tenantId, string $referenceNo): ?JournalEntry
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('reference_no', $referenceNo)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findByDateRange(int $tenantId, string $startDate, string $endDate): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get()
            ->map(fn (JournalEntryModel $m) => $this->toEntity($m));
    }

    public function findByStatus(int $tenantId, string $status): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', $status)
            ->orderBy('date')
            ->get()
            ->map(fn (JournalEntryModel $m) => $this->toEntity($m));
    }

    public function create(array $data): JournalEntry
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?JournalEntry
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($data);

        return $this->toEntity($record->fresh());
    }

    public function delete(int $id): bool
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return false;
        }

        return (bool) $record->delete();
    }

    public function addLine(int $journalEntryId, array $lineData): JournalLine
    {
        $lineData['journal_entry_id'] = $journalEntryId;
        $record = $this->lineModel->newQuery()->create($lineData);

        return $this->toLineEntity($record);
    }

    public function getLines(int $journalEntryId): Collection
    {
        return $this->lineModel->newQuery()
            ->where('journal_entry_id', $journalEntryId)
            ->get()
            ->map(fn (JournalLineModel $m) => $this->toLineEntity($m));
    }

    private function toEntity(JournalEntryModel $model): JournalEntry
    {
        return new JournalEntry(
            id: $model->id,
            tenantId: $model->tenant_id,
            referenceNo: $model->reference_no,
            date: \DateTimeImmutable::createFromMutable($model->date->toDateTime()),
            description: $model->description,
            status: $model->status,
            type: $model->type,
            createdBy: $model->created_by,
            postedAt: $model->posted_at
                ? \DateTimeImmutable::createFromMutable($model->posted_at->toDateTime())
                : null,
            voidedAt: $model->voided_at
                ? \DateTimeImmutable::createFromMutable($model->voided_at->toDateTime())
                : null,
            voidReason: $model->void_reason,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()),
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
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()),
        );
    }
}
