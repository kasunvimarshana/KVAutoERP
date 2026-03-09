<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Inventory\Entities\StockMovement;
use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Repositories\StockMovementRepositoryInterface;
use App\Infrastructure\Persistence\Models\StockMovement as StockMovementModel;
use App\Shared\Base\BaseRepository;
use DateTimeInterface;

/**
 * Eloquent implementation of StockMovementRepositoryInterface.
 */
final class EloquentStockMovementRepository extends BaseRepository implements StockMovementRepositoryInterface
{
    protected string $modelClass = StockMovementModel::class;

    /** @var array<string> */
    protected array $searchableColumns = ['reference', 'reason', 'performed_by'];

    // ─── StockMovementRepositoryInterface ────────────────────────────────────

    public function findByProduct(string $productId, array $filters = []): array
    {
        $query = $this->newQuery()->where('product_id', $productId);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($row) => StockMovement::fromArray($row->toArray()))
            ->all();
    }

    public function findByReference(string $reference): array
    {
        return $this->newQuery()
            ->where('reference', $reference)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($row) => StockMovement::fromArray($row->toArray()))
            ->all();
    }

    public function getMovementSummary(
        string $productId,
        DateTimeInterface $from,
        DateTimeInterface $to,
    ): array {
        $rows = $this->newQuery()
            ->where('product_id', $productId)
            ->where('created_at', '>=', $from->format('Y-m-d H:i:s'))
            ->where('created_at', '<=', $to->format('Y-m-d H:i:s'))
            ->get();

        $totalIn          = 0;
        $totalOut         = 0;
        $totalAdjustments = 0;

        foreach ($rows as $row) {
            $type = StockMovementType::from($row->type instanceof StockMovementType ? $row->type->value : $row->type);

            match ($type) {
                StockMovementType::IN, StockMovementType::RELEASE, StockMovementType::RETURN
                    => $totalIn += $row->quantity,
                StockMovementType::OUT, StockMovementType::RESERVATION, StockMovementType::DAMAGE
                    => $totalOut += $row->quantity,
                StockMovementType::ADJUSTMENT
                    => $totalAdjustments += abs($row->new_quantity - $row->previous_quantity),
            };
        }

        return [
            'total_in'          => $totalIn,
            'total_out'         => $totalOut,
            'total_adjustments' => $totalAdjustments,
            'net_change'        => $totalIn - $totalOut,
            'movements_count'   => $rows->count(),
        ];
    }
}
