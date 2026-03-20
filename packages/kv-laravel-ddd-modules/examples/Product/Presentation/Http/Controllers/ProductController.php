<?php

declare(strict_types=1);

namespace LaravelDDD\Examples\Product\Presentation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LaravelDDD\Examples\Product\Application\Commands\CreateProductCommand;
use LaravelDDD\Examples\Product\Application\Handlers\CreateProductHandler;
use LaravelDDD\Examples\Product\Application\Handlers\GetProductHandler;
use LaravelDDD\Examples\Product\Application\Queries\GetProductQuery;
use LaravelDDD\Examples\Product\Presentation\Http\Requests\CreateProductRequest;
use LaravelDDD\Examples\Product\Presentation\Http\Resources\ProductResource;

/**
 * Thin controller that delegates to CQRS command/query handlers.
 */
class ProductController extends Controller
{
    public function __construct(
        private readonly CreateProductHandler $createHandler,
        private readonly GetProductHandler $getHandler,
    ) {}

    /**
     * List all products (paginated).
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // TODO: Implement a ListProductsQuery/Handler for paginated listing
        return response()->json(['data' => [], 'message' => 'TODO: implement listing']);
    }

    /**
     * Return a single product by ID.
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $dto = $this->getHandler->handle(new GetProductQuery($id));

        if ($dto === null) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return response()->json(new ProductResource($dto));
    }

    /**
     * Create a new product.
     *
     * @param  CreateProductRequest  $request
     * @return JsonResponse
     */
    public function store(CreateProductRequest $request): JsonResponse
    {
        $product = $this->createHandler->handle(new CreateProductCommand(
            name: $request->validated('name'),
            priceInCents: (int) $request->validated('price_in_cents'),
            currency: (string) $request->validated('currency', 'USD'),
        ));

        return response()->json(
            ['message' => 'Product created.', 'id' => $product->getId()->value()],
            201,
        );
    }
}
