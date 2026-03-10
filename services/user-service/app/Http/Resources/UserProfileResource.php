<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'auth_user_id' => $this->auth_user_id,
            'tenant_id'    => $this->tenant_id,
            'first_name'   => $this->first_name,
            'last_name'    => $this->last_name,
            'full_name'    => $this->full_name,
            'phone'        => $this->phone,
            'address'      => $this->address,
            'preferences'  => $this->preferences,
            'avatar_url'   => $this->avatar_url,
            'created_at'   => $this->created_at->toIso8601String(),
        ];
    }
}
