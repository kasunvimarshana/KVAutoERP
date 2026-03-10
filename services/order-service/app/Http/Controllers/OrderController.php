<?php
namespace App\Http\Controllers;
use App\Services\OrderService;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class OrderController extends Controller {
    public function __construct(private readonly OrderService $orderService) {}
    public function index(Request $request): JsonResponse {
        $orders = $this->orderService->list($this->tenantId($request), $request->all());
        $isPag = $orders instanceof \Illuminate\Pagination\AbstractPaginator;
        return response()->json(['success'=>true,'data'=>OrderResource::collection($isPag?$orders->getCollection():$orders),'meta'=>$isPag?['total'=>$orders->total(),'current_page'=>$orders->currentPage(),'per_page'=>$orders->perPage(),'last_page'=>$orders->lastPage()]:null]);
    }
    public function store(StoreOrderRequest $request): JsonResponse {
        try {
            $result = $this->orderService->create($this->tenantId($request), $request->attributes->get('auth_user_id','anonymous'), $request->validated());
            return response()->json(['success'=>$result['success'],'message'=>$result['message'],'data'=>new OrderResource($result['order'])], $result['success']?201:422);
        } catch (\RuntimeException $e) { return $this->error($e); }
    }
    public function show(Request $request, string $id): JsonResponse { try { return response()->json(new OrderResource($this->orderService->get($id,$this->tenantId($request)))); } catch (\RuntimeException $e) { return $this->error($e); } }
    public function update(UpdateOrderRequest $request, string $id): JsonResponse { try { return response()->json(new OrderResource($this->orderService->update($id,$this->tenantId($request),$request->validated()))); } catch (\RuntimeException $e) { return $this->error($e); } }
    public function destroy(Request $request, string $id): JsonResponse { try { $this->orderService->delete($id,$this->tenantId($request)); return response()->json(['success'=>true,'message'=>'Order deleted.']); } catch (\RuntimeException $e) { return $this->error($e); } }
    public function cancel(Request $request, string $id): JsonResponse { try { $result=$this->orderService->cancel($id,$this->tenantId($request)); return response()->json(['success'=>$result['success'],'message'=>$result['message'],'data'=>new OrderResource($result['order'])]); } catch (\RuntimeException $e) { return $this->error($e); } }
    public function confirm(Request $request, string $id): JsonResponse { try { return response()->json(new OrderResource($this->orderService->confirm($id,$this->tenantId($request)))); } catch (\RuntimeException $e) { return $this->error($e); } }
    private function tenantId(Request $request): string { return $request->attributes->get('tenant_id',$request->header('X-Tenant-ID','')); }
    private function error(\RuntimeException $e): JsonResponse { return response()->json(['success'=>false,'message'=>$e->getMessage()],$e->getCode()?:422); }
}
