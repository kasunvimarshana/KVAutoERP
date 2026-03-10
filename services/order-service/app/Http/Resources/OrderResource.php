<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class OrderResource extends JsonResource {
    public function toArray(Request $request): array {
        return ['id'=>$this->id,'tenant_id'=>$this->tenant_id,'user_id'=>$this->user_id,'order_number'=>$this->order_number,'status'=>$this->status,'saga_status'=>$this->saga_status,'subtotal'=>$this->subtotal,'tax'=>$this->tax,'discount'=>$this->discount,'total'=>$this->total,'shipping_address'=>$this->shipping_address,'notes'=>$this->notes,'items'=>$this->whenLoaded('items',fn()=>OrderItemResource::collection($this->items)),'confirmed_at'=>$this->confirmed_at?->toIso8601String(),'cancelled_at'=>$this->cancelled_at?->toIso8601String(),'created_at'=>$this->created_at->toIso8601String(),'updated_at'=>$this->updated_at->toIso8601String()];
    }
}
