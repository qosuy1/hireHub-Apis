<?php

namespace App\Http\Resources\v1;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeResource extends JsonResource
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
            'title' => $this->title,
            'client_name' => $this->user->full_name,
            'offers_count' => $this->offers_count,
            'tags' => $this->whenLoaded('tags', function () {
                return TagResource::collection($this->tags);
            }),
            'project_status' => $this->status,
            'project_type' => $this->type,
            'budget' => $this->formatted_budget,
            'left_days' => $this->left_days,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d h:i A'),
        ];
    }


}
