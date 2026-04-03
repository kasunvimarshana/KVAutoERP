<?php
declare(strict_types=1);
namespace Modules\Brand\Infrastructure\Http\Controllers;
use Illuminate\Http\JsonResponse;
use Modules\Brand\Application\Contracts\DeleteBrandLogoServiceInterface;
use Modules\Brand\Application\Contracts\UploadBrandLogoServiceInterface;
use Modules\Brand\Infrastructure\Http\Requests\UploadBrandLogoRequest;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;
class BrandLogoController extends AuthorizedController
{
    public function __construct(private UploadBrandLogoServiceInterface $uploadService, private DeleteBrandLogoServiceInterface $deleteService) {}
    public function store(UploadBrandLogoRequest $request, int $brandId): JsonResponse { return response()->json([]); }
    public function destroy(int $brandId): JsonResponse { return response()->json([]); }
}
