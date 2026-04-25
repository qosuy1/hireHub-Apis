<?php

namespace App\Http\Resources\v1;

use App\Enums\UserTypeEnum;
use App\Models\FreelancerProfile;
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
            'full_name' => $this->full_name,
            'username' => $this->username,
            'type' => $this->type,
            'has_profile' => $this->when($this->type === UserTypeEnum::FREELANCER->value, $this->has_profile),
            'email' => $this->email,
            'location' => $this->country?->name . ' , ' . $this->city?->name,
            'membership_date' => $this->membership_date,

            'updated_at' => $this->when($request->routeIs('api.user'), Carbon::parse($this->updated_at)->format('Y-m-d h:i:s A')),

            'profile' => $this->when(
                $this->type == UserTypeEnum::FREELANCER->value,
                new FreelancerProfileResource($this->whenLoaded('freelancerProfile')),
            ),

        ];
    }
}
