<?php
declare(strict_types=1);
namespace Modules\Brand\Infrastructure\Http\Controllers;
use Illuminate\Http\JsonResponse;
use Modules\Brand\Application\Contracts\CreateBrandServiceInterface;
use Modules\Brand\Application\Contracts\DeleteBrandServiceInterface;
use Modules\Brand\Application\Contracts\UpdateBrandServiceInterface;
use Modules\Brand\Infrastructure\Http\Requests\StoreBrandRequest;
use Modules\Brand\Infrastructure\Http\Requests\UpdateBrandRequest;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;
class BrandController extends AuthorizedController
{
    public function __construct(private CreateBrandServiceInterface $createService, private UpdateBrandServiceInterface $updateService, private DeleteBrandServiceInterface $deleteService) {}
    public function index(): JsonResponse { return response()->json([]); }
    public function show(int $id): JsonResponse { return response()->json([]); }
    public function store(StoreBrandRequest $request): JsonResponse { return response()->json([]); }
    public function update(UpdateBrandRequest $request, int $id): JsonResponse { return response()->json([]); }
    public function destroy(int $id): JsonResponse { return response()->json([]); }
}
