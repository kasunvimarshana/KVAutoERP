<?php

namespace Modules\Core\Application\Services;

use Modules\Core\Domain\Contracts\Repositories\RepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

abstract class BaseService
{
    protected RepositoryInterface $repository;
    protected array $events = [];

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Execute the service with automatic transaction wrapping.
     */
    public function execute(array $data = [])
    {
        return DB::transaction(function () use ($data) {
            $result = $this->handle($data);
            $this->dispatchEvents();
            return $result;
        });
    }

    /**
     * The actual business logic to be implemented by child classes.
     */
    abstract protected function handle(array $data);

    /**
     * Register domain events to be dispatched after transaction.
     */
    protected function dispatchEvents(): void
    {
        foreach ($this->events as $event) {
            Event::dispatch($event);
        }
        $this->events = [];
    }

    /**
     * Add an event to be dispatched after transaction.
     */
    protected function addEvent($event): void
    {
        $this->events[] = $event;
    }

    /**
     * Find a record by ID.
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }

    /**
     * List records with filters and pagination.
     */
    public function list(array $filters = [], ?int $perPage = null, int $page = 1, ?string $sort = null, ?string $include = null)
    {
        $perPage = $perPage ?? config('core.pagination.per_page', 15);
        $pageName = config('core.pagination.page_name', 'page');

        $repo = clone $this->repository;

        foreach ($filters as $field => $value) {
            if (is_string($value) && str_contains($value, '%')) {
                $repo->where($field, 'like', $value);
            } else {
                $repo->where($field, $value);
            }
        }

        if ($sort) {
            [$column, $direction] = explode(':', $sort);
            $repo->orderBy($column, $direction ?? 'asc');
        }

        if ($include) {
            $repo->with(explode(',', $include));
        }

        return $repo->paginate($perPage, ['*'], $pageName, $page);
    }

    /**
     * Update a record.
     */
    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $result = $this->handleUpdate($id, $data);
            $this->dispatchEvents();
            return $result;
        });
    }

    /**
     * Delete a record.
     */
    public function delete($id)
    {
        return DB::transaction(function () use ($id) {
            $result = $this->handleDelete($id);
            $this->dispatchEvents();
            return $result;
        });
    }

    protected function handleUpdate($id, array $data)
    {
        $model = $this->repository->find($id);
        if (!$model) {
            throw new \RuntimeException('Record not found');
        }
        $this->repository->update($id, $data);
        return $this->repository->find($id);
    }

    protected function handleDelete($id)
    {
        return $this->repository->delete($id);
    }
}
