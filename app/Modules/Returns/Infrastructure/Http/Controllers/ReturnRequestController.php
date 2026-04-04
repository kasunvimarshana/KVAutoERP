<?php
declare(strict_types=1);
namespace Modules\Returns\Infrastructure\Http\Controllers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Returns\Application\Contracts\ProcessReturnServiceInterface;
use Modules\Returns\Domain\RepositoryInterfaces\ReturnRequestRepositoryInterface;
class ReturnRequestController extends Controller {
    public function __construct(
        private readonly ReturnRequestRepositoryInterface $repo,
        private readonly ProcessReturnServiceInterface $processService,
    ) {}
    public function index(Request $r): JsonResponse { return response()->json($this->repo->findByTenant((int)$r->input('tenant_id'),$r->only(['return_type','status']))); }
    public function show(int $id): JsonResponse { $ret=$this->repo->findById($id); return response()->json($ret??['message'=>'Not found'],$ret?200:404); }
    public function store(Request $r): JsonResponse { return response()->json($this->repo->create(array_merge($r->except('lines'),['status'=>'pending']),$r->input('lines',[])),201); }
    public function approve(Request $r, int $id): JsonResponse { return response()->json($this->processService->approve($id,(int)$r->input('processed_by',0))); }
    public function reject(Request $r, int $id): JsonResponse { return response()->json($this->processService->reject($id,(int)$r->input('processed_by',0))); }
    public function destroy(int $id): JsonResponse { $this->repo->delete($id); return response()->json(null,204); }
}
