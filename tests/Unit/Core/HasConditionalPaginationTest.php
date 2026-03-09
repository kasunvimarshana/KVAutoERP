<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\Traits\HasConditionalPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\TestCase;

/**
 * Tests for HasConditionalPagination trait.
 */
class HasConditionalPaginationTest extends TestCase
{
    use HasConditionalPagination;

    // -------------------------------------------------------------------------
    //  paginateIterable
    // -------------------------------------------------------------------------

    public function test_returns_all_items_when_per_page_is_null(): void
    {
        $data   = range(1, 10);
        $result = $this->paginateIterable($data, null, 1);

        $this->assertIsArray($result);
        $this->assertCount(10, $result);
    }

    public function test_returns_paginator_when_per_page_is_given(): void
    {
        $data   = range(1, 25);
        $result = $this->paginateIterable($data, 10, 1);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(25, $result->total());
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(3, $result->lastPage());
    }

    public function test_correct_items_on_second_page(): void
    {
        $data   = range(1, 25);
        $result = $this->paginateIterable($data, 10, 2);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(2, $result->currentPage());
        $items = $result->items();
        $this->assertCount(10, $items);
        $this->assertEquals(11, $items[0]); // second page starts at 11
    }

    public function test_last_page_has_remaining_items(): void
    {
        $data   = range(1, 25);
        $result = $this->paginateIterable($data, 10, 3);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(5, $result->items());
    }

    public function test_handles_collections(): void
    {
        $data   = collect(range(1, 5));
        $result = $this->paginateIterable($data, 3, 1);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(5, $result->total());
        $this->assertCount(3, $result->items());
    }

    public function test_handles_generator(): void
    {
        $gen = (function () {
            for ($i = 1; $i <= 5; $i++) {
                yield $i;
            }
        })();

        $result = $this->paginateIterable($gen, null, 1);

        $this->assertIsArray($result);
        $this->assertCount(5, $result);
    }

    public function test_returns_empty_array_for_empty_input(): void
    {
        $result = $this->paginateIterable([], null, 1);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_zero_per_page_returns_all_items(): void
    {
        $data   = range(1, 10);
        $result = $this->paginateIterable($data, 0, 1);

        $this->assertIsArray($result);
        $this->assertCount(10, $result);
    }

    // -------------------------------------------------------------------------
    //  resolvePaginationParams
    // -------------------------------------------------------------------------

    public function test_resolve_pagination_params_with_per_page_and_page(): void
    {
        $params = ['per_page' => '15', 'page' => '3'];
        $result = $this->resolvePaginationParams($params);

        $this->assertEquals(15, $result['per_page']);
        $this->assertEquals(3, $result['page']);
    }

    public function test_resolve_pagination_params_without_per_page_returns_null(): void
    {
        $params = ['page' => '2'];
        $result = $this->resolvePaginationParams($params);

        $this->assertNull($result['per_page']);
        $this->assertEquals(2, $result['page']);
    }

    public function test_resolve_pagination_params_supports_limit_alias(): void
    {
        $params = ['limit' => '20', 'page' => '1'];
        $result = $this->resolvePaginationParams($params);

        $this->assertEquals(20, $result['per_page']);
    }

    public function test_resolve_pagination_params_page_defaults_to_one(): void
    {
        $result = $this->resolvePaginationParams([]);

        $this->assertEquals(1, $result['page']);
    }
}
