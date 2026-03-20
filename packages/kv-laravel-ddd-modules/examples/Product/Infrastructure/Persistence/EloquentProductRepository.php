<?php

declare(strict_types=1);

namespace LaravelDDD\Examples\Product\Infrastructure\Persistence;

use LaravelDDD\Examples\Product\Domain\Entities\Product;
use LaravelDDD\Examples\Product\Domain\Repositories\ProductRepositoryInterface;

/**
 * Eloquent-based implementation of ProductRepositoryInterface.
 *
 * TODO: Replace stub implementations with real Eloquent queries.
 * TODO: Set $model to your Eloquent model class.
 */
class EloquentProductRepository implements ProductRepositoryInterface
{
    /**
     * The Eloquent model class name.
     * TODO: Set this to your actual Eloquent model, e.g. \App\Models\Product::class
     *
     * @var string
     */
    protected string $model = '';

    /**
     * {@inheritdoc}
     *
     * TODO: Implement using $this->model::find($id) and map to Product entity.
     */
    public function findById(mixed $id): ?object
    {
        // TODO: Implement:
        // $model = ($this->model)::find($id);
        // return $model ? $this->toEntity($model) : null;
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * TODO: Implement persist logic (insert or update).
     */
    public function save(object $entity): void
    {
        // TODO: Map Product entity to Eloquent model and call save()
    }

    /**
     * {@inheritdoc}
     *
     * TODO: Implement using ($this->model)::destroy($id).
     */
    public function delete(mixed $id): void
    {
        // TODO: ($this->model)::destroy($id);
    }

    /**
     * {@inheritdoc}
     *
     * TODO: Implement using ($this->model)::where('name', $name)->first().
     */
    public function findByName(string $name): ?Product
    {
        // TODO: Implement with Eloquent where query
        return null;
    }
}
