<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Infrastructure\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

/**
 * Base Repository Unit Tests.
 *
 * Tests the core pagination, filtering, searching, and sorting logic.
 */
class BaseRepositoryTest extends TestCase
{
    private ConcreteRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ConcreteRepository();
    }

    // =========================================================================
    // Conditional Pagination Tests
    // =========================================================================

    public function test_per_page_is_capped_at_max(): void
    {
        // Access protected method via reflection
        $reflect = new \ReflectionMethod($this->repository, 'resolvePerPage');
        $reflect->setAccessible(true);

        $result = $reflect->invoke($this->repository, ['per_page' => 9999]);

        $this->assertLessThanOrEqual(200, $result);
        $this->assertEquals(200, $result);
    }

    public function test_per_page_defaults_when_invalid_value_given(): void
    {
        $reflect = new \ReflectionMethod($this->repository, 'resolvePerPage');
        $reflect->setAccessible(true);

        $result = $reflect->invoke($this->repository, ['per_page' => -5]);

        $this->assertEquals(15, $result);
    }

    public function test_page_defaults_to_1_when_invalid(): void
    {
        $reflect = new \ReflectionMethod($this->repository, 'resolvePage');
        $reflect->setAccessible(true);

        $result = $reflect->invoke($this->repository, ['page' => -1]);

        $this->assertEquals(1, $result);
    }

    public function test_page_returns_provided_value(): void
    {
        $reflect = new \ReflectionMethod($this->repository, 'resolvePage');
        $reflect->setAccessible(true);

        $result = $reflect->invoke($this->repository, ['page' => 3]);

        $this->assertEquals(3, $result);
    }

    // =========================================================================
    // Paginate Collection Tests
    // =========================================================================

    public function test_paginate_collection_returns_array_without_per_page(): void
    {
        $items  = [['id' => 1], ['id' => 2], ['id' => 3]];
        $result = $this->repository->paginateCollection($items, []);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    public function test_paginate_collection_returns_paginator_with_per_page(): void
    {
        $items  = range(1, 20);
        $result = $this->repository->paginateCollection($items, ['per_page' => 5, 'page' => 1]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(5, $result->perPage());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(4, $result->lastPage());
    }

    public function test_paginate_collection_handles_api_response_data(): void
    {
        // Simulates cross-service data access (API response array)
        $apiResponse = array_map(
            fn ($i) => ['id' => $i, 'name' => "Item {$i}"],
            range(1, 30),
        );

        $result = $this->repository->paginateCollection($apiResponse, ['per_page' => 10, 'page' => 2]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(2, $result->currentPage());
        $this->assertEquals(10, $result->count());
        $this->assertEquals(30, $result->total());
    }

    public function test_paginate_collection_handles_laravel_collection(): void
    {
        $collection = collect(range(1, 15));
        $result     = $this->repository->paginateCollection($collection, ['per_page' => 10]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(15, $result->total());
    }

    public function test_paginate_collection_returns_correct_slice(): void
    {
        $items  = range(1, 10);
        $result = $this->repository->paginateCollection($items, ['per_page' => 3, 'page' => 2]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        // Page 2, 3 items: items 4, 5, 6
        $this->assertEquals([4, 5, 6], $result->items());
    }
}

/**
 * Concrete test double for BaseRepository.
 */
class ConcreteRepository extends BaseRepository
{
    protected array $searchableColumns  = ['name', 'description'];
    protected array $filterableColumns  = ['status', 'category_id'];
    protected string $defaultSortBy     = 'created_at';

    protected function resolveModel(): Model
    {
        return new class extends Model
        {
            protected $table = 'test_models';

            public $timestamps = false;
        };
    }
}
