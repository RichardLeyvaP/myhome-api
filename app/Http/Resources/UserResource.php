<?php

namespace App\Http\Resources;

use App\Enums\RoleEnum;
use App\Enums\StatusEnum;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->when(optional(auth()->user())->isAdmin(), $this->email),
            'email_verified_at' => $this->whenNotNull($this->email_verified_at),
            'role' => $this->role,
            'status' => $this->status,
            'created_at' => Carbon::parse($this->created_at)->locale(config('app.locale'))->isoFormat('DD MMM Y hh:mm:ss A'),
            'updated_at' => Carbon::parse($this->updated_at)->locale(config('app.locale'))->isoFormat('DD MMM Y hh:mm:ss A'),
            //Complementario
            'role_text' => optional(RoleEnum::tryFrom($this->role))->label(),
            'status_text' => optional(StatusEnum::tryFrom($this->status))->label(),
            //Relaciones
            //'role' => new RoleResource($this->whenLoaded('role')),
            //Stripe            
            //'subscriptions' => $this->whenNotNull($this->_subscriptions),
            //'payment_methods' => $this->whenNotNull($this->payment_methods)
        ];
    }
}
