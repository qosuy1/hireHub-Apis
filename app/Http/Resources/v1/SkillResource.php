<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            // Only present when loaded through the freelancer_profile_skill pivot table.
            'experience_years' => $this->whenPivotLoaded('freelancer_profile_skill', function () {
                return $this->pivot->experience_years;
            }),
        ];
    }
}
